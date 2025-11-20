<?php
/**
 * Testimonial Model
 */

namespace App\Models;

use App\Core\Model;

class Testimonial extends Model
{
    protected $table = 'testimonials';

    public function getPublished($limit = 6)
    {
        return $this->findAll(['status' => 'published'], 'sort_order ASC, created_at DESC', $limit);
    }
}


