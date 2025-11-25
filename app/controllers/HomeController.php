<?php
/**
 * Home Controller
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helper;
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
        $newsletterCsrfField = $this->csrf->getTokenField();

        // Get main categories for navigation (Food, As E Dey Hot, Drinks)
        $mainCategories = $categoryModel->getAllWithCount();
        
        $data = [
            'featuredProducts' => $productModel->getFeatured(8),
            'categories' => $mainCategories,
            'mainCategories' => array_slice($mainCategories, 0, 3), // Top 3 for nav
            'heroSlides' => $heroSlideModel->getPublished(),
            'testimonials' => $testimonialModel->getPublished(6),
            'events' => $eventModel->getUpcoming(3),
            'newsletterCsrfField' => $newsletterCsrfField,
            'businessHours' => Helper::getSetting('business_hours', 'Mon-Sat: 11AM-10PM, Sun: 12PM-9PM'),
            'contactEmail' => Helper::getSetting('site_email', 'info@pembinapint.com'),
            'contactPhone' => Helper::getSetting('site_phone', '(204) XXX-XXXX'),
            'businessAddress' => Helper::getSetting('site_address', '282 Loren Drive, Morden, Manitoba, Canada')
        ];

        $this->render('home/index', $data);
    }
}

