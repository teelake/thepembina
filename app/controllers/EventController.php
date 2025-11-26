<?php
/**
 * Public Events Controller
 * Customer-facing event calendar / listing
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Event;

class EventController extends Controller
{
    /**
     * Show full events calendar / listing
     */
    public function index()
    {
        $eventModel = new Event();

        // Fetch upcoming events (all upcoming, ordered by date)
        $events = $eventModel->findAll(
            ['status' => 'upcoming'],
            'event_date ASC'
        );

        $data = [
            'events' => $events,
            'page_title' => 'Events & Cultural Nights',
            'meta_description' => 'See upcoming events, cultural nights and special experiences at The Pembina Pint & Restaurant.'
        ];

        $this->render('events/index', $data);
    }
}


