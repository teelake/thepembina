<?php
/**
 * Admin Hero Slide Controller
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\HeroSlide;
use App\Core\Helper;
use App\Core\AuditTrail;

class HeroSlideController extends Controller
{
    private $heroSlideModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin', 'data_entry']);
        $this->heroSlideModel = new HeroSlide();
    }

    public function index()
    {
        $slides = $this->heroSlideModel->findAll([], 'sort_order ASC, created_at DESC');

        $this->render('admin/hero-slides/index', [
            'slides' => $slides,
            'page_title' => 'Hero Slider',
            'current_page' => 'hero_slides'
        ]);
    }

    public function create()
    {
        $this->render('admin/hero-slides/form', [
            'page_title' => 'Create Hero Slide',
            'current_page' => 'hero_slides'
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/hero-slides');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/hero-slides?error=Invalid security token');
            return;
        }

        $data = $this->sanitizeData();

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = Helper::uploadFile($_FILES['image'], 'uploads/hero', ALLOWED_IMAGE_TYPES);
            if ($imagePath) {
                $data['image'] = $imagePath;
            }
        }

        $slideId = $this->heroSlideModel->create($data);

        if ($slideId) {
            AuditTrail::log('hero_slide_create', 'hero_slide', $slideId, 'Created hero slide');
            $this->redirect('/admin/hero-slides?success=Slide created successfully');
        } else {
            $this->redirect('/admin/hero-slides?error=Failed to create slide');
        }
    }

    public function edit()
    {
        $id = (int)($this->params['id'] ?? 0);
        $slide = $this->heroSlideModel->find($id);

        if (!$slide) {
            $this->redirect('/admin/hero-slides?error=Slide not found');
            return;
        }

        $this->render('admin/hero-slides/form', [
            'slide' => $slide,
            'page_title' => 'Edit Hero Slide',
            'current_page' => 'hero_slides'
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/hero-slides');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $slide = $this->heroSlideModel->find($id);

        if (!$slide) {
            $this->redirect('/admin/hero-slides?error=Slide not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect("/admin/hero-slides/{$id}/edit?error=Invalid security token");
            return;
        }

        $data = $this->sanitizeData();

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if (!empty($slide['image'])) {
                Helper::deleteFile($slide['image']);
            }
            $imagePath = Helper::uploadFile($_FILES['image'], 'uploads/hero', ALLOWED_IMAGE_TYPES);
            if ($imagePath) {
                $data['image'] = $imagePath;
            }
        }

        if ($this->heroSlideModel->update($id, $data)) {
            AuditTrail::log('hero_slide_update', 'hero_slide', $id, 'Updated hero slide');
            $this->redirect('/admin/hero-slides?success=Slide updated successfully');
        } else {
            $this->redirect("/admin/hero-slides/{$id}/edit?error=Failed to update slide");
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/hero-slides');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $slide = $this->heroSlideModel->find($id);

        if (!$slide) {
            $this->redirect('/admin/hero-slides?error=Slide not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/hero-slides?error=Invalid security token');
            return;
        }

        if (!empty($slide['image'])) {
            Helper::deleteFile($slide['image']);
        }

        if ($this->heroSlideModel->delete($id)) {
            AuditTrail::log('hero_slide_delete', 'hero_slide', $id, 'Deleted hero slide');
            $this->redirect('/admin/hero-slides?success=Slide deleted successfully');
        } else {
            $this->redirect('/admin/hero-slides?error=Failed to delete slide');
        }
    }

    private function sanitizeData()
    {
        return [
            'title' => $this->post('title'),
            'subtitle' => $this->post('subtitle'),
            'description' => $this->post('description'),
            'button_text' => $this->post('button_text'),
            'button_link' => $this->post('button_link'),
            'status' => $this->post('status', 'published'),
            'sort_order' => (int)$this->post('sort_order', 0)
        ];
    }
}


