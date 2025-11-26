<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Page;
use App\Core\AuditTrail;
use App\Core\Helper;

class PageController extends Controller
{
    private $pageModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin']);
        $this->pageModel = new Page();
    }

    public function index()
    {
        $pages = $this->pageModel->findAll([], 'created_at DESC');
        $this->render('admin/pages/index', [
            'pages' => $pages,
            'page_title' => 'Pages',
            'current_page' => 'pages',
            'use_tinymce' => true,
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function create()
    {
        $this->render('admin/pages/form', [
            'page_title' => 'Create Page',
            'current_page' => 'pages',
            'use_tinymce' => true,
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/pages');
            return;
        }
        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/pages?error=Invalid security token');
            return;
        }

        $data = [
            'title' => $this->post('title'),
            'slug' => $this->post('slug') ?: Helper::slugify($this->post('title')),
            'content' => $this->post('content'),
            'status' => $this->post('status', 'published'),
            'meta_title' => $this->post('meta_title'),
            'meta_description' => $this->post('meta_description')
        ];

        $id = $this->pageModel->create($data);
        if ($id) {
            AuditTrail::log('page_create', 'page', $id, 'Created page');
            $this->redirect('/admin/pages?success=Page created successfully');
        } else {
            $this->redirect('/admin/pages?error=Failed to create page');
        }
    }

    public function edit()
    {
        $id = (int)($this->params['id'] ?? 0);
        $page = $this->pageModel->find($id);
        if (!$page) {
            $this->redirect('/admin/pages?error=Page not found');
            return;
        }

        $this->render('admin/pages/form', [
            'pageData' => $page,
            'page_title' => 'Edit Page',
            'current_page' => 'pages',
            'use_tinymce' => true,
            'csrfField' => $this->csrf->getTokenField()
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/pages');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $page = $this->pageModel->find($id);
        if (!$page) {
            $this->redirect('/admin/pages?error=Page not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect("/admin/pages/{$id}/edit?error=Invalid security token");
            return;
        }

        $data = [
            'title' => $this->post('title'),
            'slug' => $this->post('slug') ?: Helper::slugify($this->post('title')),
            'content' => $this->post('content'),
            'status' => $this->post('status', 'published'),
            'meta_title' => $this->post('meta_title'),
            'meta_description' => $this->post('meta_description')
        ];

        if ($this->pageModel->update($id, $data)) {
            AuditTrail::log('page_update', 'page', $id, 'Updated page');
            $this->redirect('/admin/pages?success=Page updated successfully');
        } else {
            $this->redirect("/admin/pages/{$id}/edit?error=Failed to update page");
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/pages');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $page = $this->pageModel->find($id);
        if (!$page) {
            $this->redirect('/admin/pages?error=Page not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/pages?error=Invalid security token');
            return;
        }

        if ($this->pageModel->delete($id)) {
            AuditTrail::log('page_delete', 'page', $id, 'Deleted page');
            $this->redirect('/admin/pages?success=Page deleted successfully');
        } else {
            $this->redirect('/admin/pages?error=Failed to delete page');
        }
    }

    /**
     * Upload image for TinyMCE editor
     * Returns JSON response for TinyMCE
     */
    public function uploadImage()
    {
        // Only allow POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        // Note: CSRF check is optional for file uploads via AJAX
        // The requireRole() in constructor already ensures admin-only access

        // Check if file was uploaded
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = 'No file uploaded or upload error occurred';
            if (isset($_FILES['file']['error'])) {
                switch ($_FILES['file']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errorMessage = 'File size exceeds maximum allowed size (5MB)';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $errorMessage = 'File was only partially uploaded';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $errorMessage = 'No file was uploaded';
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $errorMessage = 'Missing temporary folder';
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $errorMessage = 'Failed to write file to disk';
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $errorMessage = 'File upload stopped by extension';
                        break;
                }
            }
            
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => $errorMessage]);
            exit;
        }

        $file = $_FILES['file'];

        // Validate file type (images only)
        $allowedTypes = defined('ALLOWED_IMAGE_TYPES') ? ALLOWED_IMAGE_TYPES : ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid file type. Only images (JPEG, PNG, GIF, WebP) are allowed.']);
            exit;
        }

        // Validate file size
        $maxSize = defined('MAX_UPLOAD_SIZE') ? MAX_UPLOAD_SIZE : 5242880; // 5MB default
        if ($file['size'] > $maxSize) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'File size exceeds maximum allowed size (5MB)']);
            exit;
        }

        // Upload file to uploads/images directory
        $uploadDir = 'uploads/images';
        $uploadedFile = Helper::uploadFile($file, $uploadDir, $allowedTypes);

        if ($uploadedFile === false) {
            \App\Core\Logger::error('TinyMCE image upload failed', [
                'file_name' => $file['name'],
                'file_size' => $file['size'],
                'mime_type' => $mimeType,
                'user_id' => $_SESSION['user_id'] ?? null,
                'url' => $_SERVER['REQUEST_URI'] ?? null
            ]);

            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Failed to upload file. Please try again.']);
            exit;
        }

        // Log successful upload
        \App\Core\Logger::info('TinyMCE image uploaded', [
            'file_name' => $file['name'],
            'uploaded_path' => $uploadedFile,
            'file_size' => $file['size'],
            'mime_type' => $mimeType,
            'user_id' => $_SESSION['user_id'] ?? null,
            'url' => $_SERVER['REQUEST_URI'] ?? null
        ]);

        // Return JSON response in TinyMCE format
        $imageUrl = BASE_URL . '/public/' . $uploadedFile;
        
        header('Content-Type: application/json');
        echo json_encode([
            'location' => $imageUrl
        ]);
        exit;
    }
}


