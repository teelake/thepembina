<?php
/**
 * Home Controller
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Category;

class HomeController extends Controller
{
    /**
     * Home page
     */
    public function index()
    {
        $productModel = new Product();
        $categoryModel = new Category();

        $data = [
            'featuredProducts' => $productModel->getFeatured(8),
            'categories' => $categoryModel->getAllWithCount()
        ];

        $this->render('home/index', $data);
    }
}

