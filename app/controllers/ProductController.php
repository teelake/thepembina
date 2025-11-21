<?php
/**
 * Product Controller
 * Product detail pages
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Models\Product;

class ProductController extends Controller
{
    private $productModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->productModel = new Product();
    }

    /**
     * Product detail page
     */
    public function view()
    {
        $slug = $this->params['slug'] ?? '';
        error_log(sprintf('[ProductController] Requested slug: %s | URI: %s', $slug ?: '(empty)', $_SERVER['REQUEST_URI'] ?? 'n/a'));

        try {
            $product = $this->productModel->findBySlug($slug);
        } catch (\Throwable $e) {
            error_log(sprintf('[ProductController] Product lookup failed for slug "%s": %s', $slug, $e->getMessage()));
            Logger::error('Product lookup failed', [
                'slug' => $slug,
                'url' => $_SERVER['REQUEST_URI'] ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
        
        if (!$product) {
            $context = [
                'slug' => $slug,
                'url' => $_SERVER['REQUEST_URI'] ?? null,
            ];
            error_log(sprintf('[ProductController] Product slug not found: %s', json_encode($context)));
            Logger::warning('Product slug not found', $context);
            
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        // Get product options
        $options = $this->productModel->getOptions($product['id']);

        // Get related products
        $relatedProducts = [];
        if ($product['category_id']) {
            $relatedProducts = $this->productModel->getByCategory($product['category_id'], 4);
            // Remove current product
            $relatedProducts = array_filter($relatedProducts, function($p) use ($product) {
                return $p['id'] != $product['id'];
            });
            $relatedProducts = array_slice($relatedProducts, 0, 4);
        }

        $data = [
            'product' => $product,
            'options' => $options,
            'relatedProducts' => $relatedProducts,
            'page_title' => $product['name'],
            'meta_description' => $product['meta_description'] ?? $product['short_description'] ?? '',
            'csrfField' => $this->csrf->getTokenField()
        ];

        try {
            $this->render('product/view', $data);
        } catch (\Throwable $e) {
            error_log(sprintf('[ProductController] View rendering failed for slug "%s": %s', $slug, $e->getMessage()));
            Logger::error('Product view rendering failed', [
                'slug' => $slug,
                'product_id' => $product['id'] ?? null,
                'url' => $_SERVER['REQUEST_URI'] ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}

