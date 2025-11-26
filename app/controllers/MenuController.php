<?php
/**
 * Menu Controller
 * Customer-facing menu pages
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Category;

class MenuController extends Controller
{
    private $productModel;
    private $categoryModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }

    /**
     * Menu listing page
     */
    public function index()
    {
        $categories = $this->categoryModel->getAllWithCount();
        
        // Get featured products
        $featuredProducts = $this->productModel->getFeatured(6);
        
        $data = [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'csrfField' => $this->csrf->getTokenField(),
            'page_title' => 'Our Menu',
            'meta_description' => 'Browse our authentic African and Nigerian cuisine menu. From Jollof Rice to Suya, discover delicious flavors at The Pembina Pint.'
        ];

        $this->render('menu/index', $data);
    }

    /**
     * Category view
     */
    public function view()
    {
        $slug = $this->params['slug'] ?? '';
        $category = $this->categoryModel->findBySlug($slug);
        
        if (!$category) {
            throw new \Exception("Category not found", 404);
        }

        // Get filter parameters
        $filters = [
            'search' => trim($this->get('search', '')),
            'sort' => $this->get('sort', 'default'),
            'min_price' => $this->get('min_price', ''),
            'max_price' => $this->get('max_price', ''),
            'availability' => $this->get('availability', ''),
            'featured' => $this->get('featured', '')
        ];
        
        // Clean up empty filters
        $filters = array_filter($filters, function($value) {
            return $value !== '' && $value !== 'default';
        });

        $page = (int)($this->get('page', 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $products = $this->productModel->getByCategory($category['id'], $filters, $limit, $offset);
        $totalProducts = $this->productModel->countByCategory($category['id'], $filters);
        $totalPages = ceil($totalProducts / $limit);
        
        // Get price range for filter
        $priceRange = $this->productModel->getPriceRange($category['id']);

        $data = [
            'category' => $category,
            'products' => $products,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalProducts' => $totalProducts,
            'filters' => $filters,
            'priceRange' => $priceRange,
            'csrfField' => $this->csrf->getTokenField(),
            'page_title' => $category['name'],
            'meta_description' => $category['meta_description'] ?? $category['description'] ?? ''
        ];

        $this->render('menu/category', $data);
    }
}

