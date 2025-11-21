<?php
/**
 * Product Controller
 * Product detail pages
 */

namespace App\Controllers;

use App\Core\Controller;
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
        $product = $this->productModel->findBySlug($slug);
        
        if (!$product) {
            throw new \Exception("Product not found", 404);
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

        $this->render('product/view', $data);
    }
}

