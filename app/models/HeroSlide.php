<?php
/**
 * HeroSlide Model
 */

namespace App\Models;

use App\Core\Model;

class HeroSlide extends Model
{
    protected $table = 'hero_slides';

    /**
     * Get published slides ordered by sort_order
     *
     * @return array
     */
    public function getPublished()
    {
        return $this->findAll(['status' => 'published'], 'sort_order ASC, created_at DESC');
    }
}


