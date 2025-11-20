<?php
/**
 * Home Controller
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\HeroSlide;

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

        $data = [
            'featuredProducts' => $productModel->getFeatured(8),
            'categories' => $categoryModel->getAllWithCount(),
            'heroSlides' => $heroSlideModel->getPublished()
        ];

        $this->render('home/index', $data);
    }
}

