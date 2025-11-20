<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Testimonial;
use App\Core\Helper;
use App\Core\AuditTrail;

class TestimonialController extends Controller
{
    private $testimonialModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin', 'data_entry']);
        $this->testimonialModel = new Testimonial();
    }

    public function index()
    {
        $testimonials = $this->testimonialModel->findAll([], 'sort_order ASC, created_at DESC');
        $this->render('admin/testimonials/index', [
            'testimonials' => $testimonials,
            'page_title' => 'Testimonials',
            'current_page' => 'testimonials'
        ]);
    }

    public function create()
    {
        $this->render('admin/testimonials/form', [
            'page_title' => 'Add Testimonial',
            'current_page' => 'testimonials'
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/testimonials');
            return;
        }
        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/testimonials?error=Invalid security token');
            return;
        }

        $data = [
            'name' => $this->post('name'),
            'title' => $this->post('title'),
            'message' => $this->post('message'),
            'rating' => (int)$this->post('rating', 5),
            'status' => $this->post('status', 'published'),
            'sort_order' => (int)$this->post('sort_order', 0)
        ];

        $id = $this->testimonialModel->create($data);
        if ($id) {
            AuditTrail::log('testimonial_create', 'testimonial', $id, 'Created testimonial');
            $this->redirect('/admin/testimonials?success=Testimonial created successfully');
        } else {
            $this->redirect('/admin/testimonials?error=Failed to create testimonial');
        }
    }

    public function edit()
    {
        $id = (int)($this->params['id'] ?? 0);
        $testimonial = $this->testimonialModel->find($id);
        if (!$testimonial) {
            $this->redirect('/admin/testimonials?error=Testimonial not found');
            return;
        }

        $this->render('admin/testimonials/form', [
            'testimonial' => $testimonial,
            'page_title' => 'Edit Testimonial',
            'current_page' => 'testimonials'
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/testimonials');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $testimonial = $this->testimonialModel->find($id);
        if (!$testimonial) {
            $this->redirect('/admin/testimonials?error=Testimonial not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect("/admin/testimonials/{$id}/edit?error=Invalid security token");
            return;
        }

        $data = [
            'name' => $this->post('name'),
            'title' => $this->post('title'),
            'message' => $this->post('message'),
            'rating' => (int)$this->post('rating', 5),
            'status' => $this->post('status', 'published'),
            'sort_order' => (int)$this->post('sort_order', 0)
        ];

        if ($this->testimonialModel->update($id, $data)) {
            AuditTrail::log('testimonial_update', 'testimonial', $id, 'Updated testimonial');
            $this->redirect('/admin/testimonials?success=Testimonial updated successfully');
        } else {
            $this->redirect("/admin/testimonials/{$id}/edit?error=Failed to update testimonial");
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/testimonials');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $testimonial = $this->testimonialModel->find($id);
        if (!$testimonial) {
            $this->redirect('/admin/testimonials?error=Testimonial not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/testimonials?error=Invalid security token');
            return;
        }

        if ($this->testimonialModel->delete($id)) {
            AuditTrail::log('testimonial_delete', 'testimonial', $id, 'Deleted testimonial');
            $this->redirect('/admin/testimonials?success=Testimonial deleted successfully');
        } else {
            $this->redirect('/admin/testimonials?error=Failed to delete testimonial');
        }
    }
}

<?php
/**
 * Admin Testimonial Controller
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Testimonial;
use App\Core\Helper;
use App\Core\AuditTrail;

class TestimonialController extends Controller
{
    private $testimonialModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin', 'data_entry']);
        $this->testimonialModel = new Testimonial();
    }

    public function index()
    {
        $testimonials = $this->testimonialModel->findAll([], 'sort_order ASC, created_at DESC');

        $this->render('admin/testimonials/index', [
            'testimonials' => $testimonials,
            'page_title' => 'Testimonials',
            'current_page' => 'testimonials'
        ]);
    }

    public function create()
    {
        $this->render('admin/testimonials/form', [
            'page_title' => 'Create Testimonial',
            'current_page' => 'testimonials'
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/testimonials');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/testimonials?error=Invalid security token');
            return;
        }

        $data = $this->collectData();

        if ($this->testimonialModel->create($data)) {
            AuditTrail::log('testimonial_create', 'testimonial', null, 'Created testimonial', $data);
            $this->redirect('/admin/testimonials?success=Testimonial added');
        } else {
            $this->redirect('/admin/testimonials?error=Failed to add testimonial');
        }
    }

    public function edit()
    {
        $id = (int)($this->params['id'] ?? 0);
        $testimonial = $this->testimonialModel->find($id);

        if (!$testimonial) {
            $this->redirect('/admin/testimonials?error=Testimonial not found');
            return;
        }

        $this->render('admin/testimonials/form', [
            'testimonial' => $testimonial,
            'page_title' => 'Edit Testimonial',
            'current_page' => 'testimonials'
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/testimonials');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $testimonial = $this->testimonialModel->find($id);

        if (!$testimonial) {
            $this->redirect('/admin/testimonials?error=Testimonial not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect("/admin/testimonials/{$id}/edit?error=Invalid security token");
            return;
        }

        $data = $this->collectData();

        if ($this->testimonialModel->update($id, $data)) {
            AuditTrail::log('testimonial_update', 'testimonial', $id, 'Updated testimonial', $data);
            $this->redirect('/admin/testimonials?success=Testimonial updated');
        } else {
            $this->redirect("/admin/testimonials/{$id}/edit?error=Failed to update testimonial");
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/testimonials');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $testimonial = $this->testimonialModel->find($id);

        if (!$testimonial) {
            $this->redirect('/admin/testimonials?error=Testimonial not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/testimonials?error=Invalid security token');
            return;
        }

        if ($this->testimonialModel->delete($id)) {
            AuditTrail::log('testimonial_delete', 'testimonial', $id, 'Deleted testimonial');
            $this->redirect('/admin/testimonials?success=Testimonial deleted');
        } else {
            $this->redirect('/admin/testimonials?error=Failed to delete testimonial');
        }
    }

    private function collectData()
    {
        return [
            'name' => $this->post('name'),
            'title' => $this->post('title'),
            'message' => $this->post('message'),
            'rating' => (int)$this->post('rating', 5),
            'status' => $this->post('status', 'published'),
            'sort_order' => (int)$this->post('sort_order', 0)
        ];
    }
}


