<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Event;
use App\Core\Helper;
use App\Core\AuditTrail;

class EventController extends Controller
{
    private $eventModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin', 'data_entry']);
        $this->eventModel = new Event();
    }

    public function index()
    {
        $events = $this->eventModel->findAll([], 'event_date ASC');
        $this->render('admin/events/index', [
            'events' => $events,
            'page_title' => 'Events & Cultural Nights',
            'current_page' => 'events'
        ]);
    }

    public function create()
    {
        $this->render('admin/events/form', [
            'page_title' => 'Add Event',
            'current_page' => 'events'
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/events');
            return;
        }
        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/events?error=Invalid security token');
            return;
        }

        $data = $this->sanitizeData();
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $path = Helper::uploadFile($_FILES['image'], 'uploads/events', ALLOWED_IMAGE_TYPES);
            if ($path) {
                $data['image'] = $path;
            }
        }

        $id = $this->eventModel->create($data);
        if ($id) {
            AuditTrail::log('event_create', 'event', $id, 'Created event');
            $this->redirect('/admin/events?success=Event created successfully');
        } else {
            $this->redirect('/admin/events?error=Failed to create event');
        }
    }

    public function edit()
    {
        $id = (int)($this->params['id'] ?? 0);
        $event = $this->eventModel->find($id);
        if (!$event) {
            $this->redirect('/admin/events?error=Event not found');
            return;
        }

        $this->render('admin/events/form', [
            'event' => $event,
            'page_title' => 'Edit Event',
            'current_page' => 'events'
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/events');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $event = $this->eventModel->find($id);
        if (!$event) {
            $this->redirect('/admin/events?error=Event not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect("/admin/events/{$id}/edit?error=Invalid security token");
            return;
        }

        $data = $this->sanitizeData();
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if (!empty($event['image'])) {
                Helper::deleteFile($event['image']);
            }
            $path = Helper::uploadFile($_FILES['image'], 'uploads/events', ALLOWED_IMAGE_TYPES);
            if ($path) {
                $data['image'] = $path;
            }
        }

        if ($this->eventModel->update($id, $data)) {
            AuditTrail::log('event_update', 'event', $id, 'Updated event');
            $this->redirect('/admin/events?success=Event updated successfully');
        } else {
            $this->redirect("/admin/events/{$id}/edit?error=Failed to update event");
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/events');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $event = $this->eventModel->find($id);
        if (!$event) {
            $this->redirect('/admin/events?error=Event not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/events?error=Invalid security token');
            return;
        }

        if (!empty($event['image'])) {
            Helper::deleteFile($event['image']);
        }

        if ($this->eventModel->delete($id)) {
            AuditTrail::log('event_delete', 'event', $id, 'Deleted event');
            $this->redirect('/admin/events?success=Event deleted successfully');
        } else {
            $this->redirect('/admin/events?error=Failed to delete event');
        }
    }

    private function sanitizeData()
    {
        return [
            'title' => $this->post('title'),
            'subtitle' => $this->post('subtitle'),
            'description' => $this->post('description'),
            'event_date' => $this->post('event_date'),
            'event_time' => $this->post('event_time'),
            'location' => $this->post('location'),
            'status' => $this->post('status', 'upcoming')
        ];
    }
}

<?php
/**
 * Admin Event Controller
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Event;
use App\Core\Helper;
use App\Core\AuditTrail;

class EventController extends Controller
{
    private $eventModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin', 'data_entry']);
        $this->eventModel = new Event();
    }

    public function index()
    {
        $events = $this->eventModel->findAll([], 'event_date ASC');

        $this->render('admin/events/index', [
            'events' => $events,
            'page_title' => 'Events & Cultural Nights',
            'current_page' => 'events'
        ]);
    }

    public function create()
    {
        $this->render('admin/events/form', [
            'page_title' => 'Add Event',
            'current_page' => 'events'
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/events');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/events?error=Invalid security token');
            return;
        }

        $data = $this->collectData();

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = Helper::uploadFile($_FILES['image'], 'uploads/events', ALLOWED_IMAGE_TYPES);
            if ($imagePath) {
                $data['image'] = $imagePath;
            }
        }

        if ($this->eventModel->create($data)) {
            AuditTrail::log('event_create', 'event', null, 'Created event', $data);
            $this->redirect('/admin/events?success=Event added');
        } else {
            $this->redirect('/admin/events?error=Failed to add event');
        }
    }

    public function edit()
    {
        $id = (int)($this->params['id'] ?? 0);
        $event = $this->eventModel->find($id);

        if (!$event) {
            $this->redirect('/admin/events?error=Event not found');
            return;
        }

        $this->render('admin/events/form', [
            'event' => $event,
            'page_title' => 'Edit Event',
            'current_page' => 'events'
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/events');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $event = $this->eventModel->find($id);

        if (!$event) {
            $this->redirect('/admin/events?error=Event not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect("/admin/events/{$id}/edit?error=Invalid security token");
            return;
        }

        $data = $this->collectData();

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if (!empty($event['image'])) {
                Helper::deleteFile($event['image']);
            }
            $imagePath = Helper::uploadFile($_FILES['image'], 'uploads/events', ALLOWED_IMAGE_TYPES);
            if ($imagePath) {
                $data['image'] = $imagePath;
            }
        }

        if ($this->eventModel->update($id, $data)) {
            AuditTrail::log('event_update', 'event', $id, 'Updated event', $data);
            $this->redirect('/admin/events?success=Event updated');
        } else {
            $this->redirect("/admin/events/{$id}/edit?error=Failed to update event");
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/events');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $event = $this->eventModel->find($id);

        if (!$event) {
            $this->redirect('/admin/events?error=Event not found');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/events?error=Invalid security token');
            return;
        }

        if (!empty($event['image'])) {
            Helper::deleteFile($event['image']);
        }

        if ($this->eventModel->delete($id)) {
            AuditTrail::log('event_delete', 'event', $id, 'Deleted event');
            $this->redirect('/admin/events?success=Event deleted');
        } else {
            $this->redirect('/admin/events?error=Failed to delete event');
        }
    }

    private function collectData()
    {
        return [
            'title' => $this->post('title'),
            'subtitle' => $this->post('subtitle'),
            'description' => $this->post('description'),
            'event_date' => $this->post('event_date'),
            'event_time' => $this->post('event_time'),
            'location' => $this->post('location'),
            'status' => $this->post('status', 'upcoming')
        ];
    }
}


