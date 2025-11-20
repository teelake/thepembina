<?php
/**
 * Home Controller
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\Testimonial;
use App\Models\Event;

class HomeController extends Controller
{
    /**
     * Home page
     */
    public function index()
    {
        $productModel = new Product();
        $categoryModel = new Category();
        $heroSlideModel = new HeroSlide();
        $testimonialModel = new Testimonial();
        $eventModel = new Event();

        $data = [
            'featuredProducts' => $productModel->getFeatured(8),
            'categories' => $categoryModel->getAllWithCount(),
            'heroSlides' => $heroSlideModel->getPublished(),
            'testimonials' => $testimonialModel->getPublished(6),
            'events' => $eventModel->getUpcoming(3)
        ];

        $this->render('home/index', $data);
    }
}

