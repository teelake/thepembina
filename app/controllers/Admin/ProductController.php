<?php
/**
 * Admin Product Controller
 */

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Core\Helper;
use App\Core\AuditTrail;

class ProductController extends Controller
{
    private $productModel;
    private $categoryModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->requireRole(['super_admin', 'admin', 'data_entry']);
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }

    /**
     * List products
     */
    public function index()
    {
        $page = (int)($this->get('page', 1));
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $products = $this->productModel->findAll([], 'created_at DESC', $limit, $offset);
        $totalProducts = $this->productModel->count();
        $totalPages = ceil($totalProducts / $limit);
        
        $data = [
            'products' => $products,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'page_title' => 'Products',
            'current_page' => 'products',
            'csrfField' => $this->csrf->getTokenField()
        ];
        
        $this->render('admin/product/index', $data);
    }

    /**
     * Create product form
     */
    public function create()
    {
        $categories = $this->categoryModel->findAll(['status' => 'active'], 'name');
        
        $data = [
            'categories' => $categories,
            'page_title' => 'Create Product',
            'current_page' => 'products',
            'csrfField' => $this->csrf->getTokenField()
        ];
        
        $this->render('admin/product/form', $data);
    }

    /**
     * Store product
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->render('admin/product/form', ['error_message' => 'Invalid security token']);
            return;
        }

        $name = trim($this->post('name'));
        $sku = trim($this->post('sku'));

        // Validate product name uniqueness
        if ($this->productModel->nameExists($name)) {
            $categories = $this->categoryModel->findAll(['status' => 'active'], 'name');
            $this->render('admin/product/form', [
                'categories' => $categories,
                'error_message' => 'A product with this name already exists. Please use a different name.',
                'formData' => $_POST,
                'page_title' => 'Create Product',
                'current_page' => 'products',
                'csrfField' => $this->csrf->getTokenField()
            ]);
            return;
        }

        // Validate SKU uniqueness (if provided)
        if (!empty($sku) && $this->productModel->skuExists($sku)) {
            $categories = $this->categoryModel->findAll(['status' => 'active'], 'name');
            $this->render('admin/product/form', [
                'categories' => $categories,
                'error_message' => 'A product with this SKU already exists. Please use a different SKU.',
                'formData' => $_POST,
                'page_title' => 'Create Product',
                'current_page' => 'products',
                'csrfField' => $this->csrf->getTokenField()
            ]);
            return;
        }

        $data = [
            'name' => $name,
            'slug' => Helper::slugify($name),
            'description' => $this->post('description'),
            'short_description' => $this->post('short_description'),
            'sku' => !empty($sku) ? $sku : null,
            'price' => (float)$this->post('price'),
            'compare_price' => $this->post('compare_price') ? (float)$this->post('compare_price') : null,
            'cost_price' => $this->post('cost_price') ? (float)$this->post('cost_price') : null,
            'category_id' => $this->post('category_id') ? (int)$this->post('category_id') : null,
            'stock_quantity' => $this->post('stock_quantity') ? (int)$this->post('stock_quantity') : null,
            'stock_status' => $this->post('stock_status', 'in_stock'),
            'manage_stock' => $this->post('manage_stock') ? 1 : 0,
            'is_featured' => $this->post('is_featured') ? 1 : 0,
            'status' => $this->post('status', 'active'),
            'sort_order' => (int)$this->post('sort_order', 0),
            'meta_title' => $this->post('meta_title'),
            'meta_description' => $this->post('meta_description')
        ];

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = Helper::uploadFile($_FILES['image'], 'uploads/products', ALLOWED_IMAGE_TYPES);
            if ($imagePath) {
                $data['image'] = $imagePath;
            }
        }

        $productId = $this->productModel->createProduct($data);
        
        if ($productId) {
            AuditTrail::log('product_create', 'product', $productId, "Created product: {$data['name']}");
            $this->redirect('/admin/products?success=Product created successfully');
        } else {
            $this->render('admin/product/form', [
                'error_message' => 'Failed to create product',
                'formData' => $data
            ]);
        }
    }

    /**
     * Edit product form
     */
    public function edit()
    {
        $id = (int)($this->params['id'] ?? 0);
        $product = $this->productModel->find($id);
        
        if (!$product) {
            throw new \Exception("Product not found", 404);
        }

        $categories = $this->categoryModel->findAll(['status' => 'active'], 'name');
        
        $data = [
            'product' => $product,
            'categories' => $categories,
            'page_title' => 'Edit Product',
            'current_page' => 'products',
            'csrfField' => $this->csrf->getTokenField()
        ];
        
        $this->render('admin/product/form', $data);
    }

    /**
     * Update product
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $product = $this->productModel->find($id);
        
        if (!$product) {
            throw new \Exception("Product not found", 404);
        }

        if (!$this->verifyCSRF()) {
            $this->redirect("/admin/products/{$id}/edit?error=Invalid security token");
            return;
        }

        $name = trim($this->post('name'));
        $sku = trim($this->post('sku'));

        // Validate product name uniqueness (excluding current product)
        if ($this->productModel->nameExists($name, $id)) {
            $categories = $this->categoryModel->findAll(['status' => 'active'], 'name');
            $this->render('admin/product/form', [
                'product' => $product,
                'categories' => $categories,
                'error_message' => 'A product with this name already exists. Please use a different name.',
                'page_title' => 'Edit Product',
                'current_page' => 'products',
                'csrfField' => $this->csrf->getTokenField()
            ]);
            return;
        }

        // Validate SKU uniqueness (if provided, excluding current product)
        if (!empty($sku) && $this->productModel->skuExists($sku, $id)) {
            $categories = $this->categoryModel->findAll(['status' => 'active'], 'name');
            $this->render('admin/product/form', [
                'product' => $product,
                'categories' => $categories,
                'error_message' => 'A product with this SKU already exists. Please use a different SKU.',
                'page_title' => 'Edit Product',
                'current_page' => 'products',
                'csrfField' => $this->csrf->getTokenField()
            ]);
            return;
        }

        $data = [
            'name' => $name,
            'slug' => Helper::slugify($name),
            'description' => $this->post('description'),
            'short_description' => $this->post('short_description'),
            'sku' => !empty($sku) ? $sku : null,
            'price' => (float)$this->post('price'),
            'compare_price' => $this->post('compare_price') ? (float)$this->post('compare_price') : null,
            'cost_price' => $this->post('cost_price') ? (float)$this->post('cost_price') : null,
            'category_id' => $this->post('category_id') ? (int)$this->post('category_id') : null,
            'stock_quantity' => $this->post('stock_quantity') ? (int)$this->post('stock_quantity') : null,
            'stock_status' => $this->post('stock_status', 'in_stock'),
            'manage_stock' => $this->post('manage_stock') ? 1 : 0,
            'is_featured' => $this->post('is_featured') ? 1 : 0,
            'status' => $this->post('status', 'active'),
            'sort_order' => (int)$this->post('sort_order', 0),
            'meta_title' => $this->post('meta_title'),
            'meta_description' => $this->post('meta_description')
        ];

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Delete old image
            if ($product['image']) {
                Helper::deleteFile($product['image']);
            }
            
            $imagePath = Helper::uploadFile($_FILES['image'], 'uploads/products', ALLOWED_IMAGE_TYPES);
            if ($imagePath) {
                $data['image'] = $imagePath;
            }
        }

        if ($this->productModel->updateProduct($id, $data)) {
            AuditTrail::log('product_update', 'product', $id, "Updated product: {$data['name']}");
            $this->redirect('/admin/products?success=Product updated successfully');
        } else {
            $this->redirect("/admin/products/{$id}/edit?error=Failed to update product");
        }
    }

    /**
     * Delete product
     */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products');
            return;
        }

        $id = (int)($this->params['id'] ?? 0);
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $this->jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
            return;
        }

        // Delete image
        if ($product['image']) {
            Helper::deleteFile($product['image']);
        }

        if ($this->productModel->delete($id)) {
            AuditTrail::log('product_delete', 'product', $id, "Deleted product: {$product['name']}");
            $this->jsonResponse(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to delete product'], 500);
        }
    }

    /**
     * Import products from Excel
     */
    public function import()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products');
            return;
        }

        if (!$this->verifyCSRF()) {
            $this->redirect('/admin/products?error=Invalid security token');
            return;
        }

        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $this->redirect('/admin/products?error=Upload a valid CSV file');
            return;
        }

        $extension = strtolower(pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            $this->redirect('/admin/products?error=Please upload a CSV file');
            return;
        }

        $handle = fopen($_FILES['import_file']['tmp_name'], 'r');
        if (!$handle) {
            $this->redirect('/admin/products?error=Unable to read uploaded file');
            return;
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            $this->redirect('/admin/products?error=CSV file is empty');
            return;
        }

        $columns = array_map('strtolower', $header);
        $nameIndex = array_search('name', $columns);
        $categoryIndex = array_search('category', $columns);
        $priceIndex = array_search('price', $columns);

        if ($nameIndex === false || $categoryIndex === false || $priceIndex === false) {
            fclose($handle);
            $this->redirect('/admin/products?error=CSV must include name, category, and price columns');
            return;
        }

        $descriptionIndex = array_search('description', $columns);
        $shortDescIndex = array_search('short_description', $columns);
        $skuIndex = array_search('sku', $columns);
        $stockIndex = array_search('stock_quantity', $columns);

        $imported = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $name = trim($row[$nameIndex] ?? '');
            if ($name === '') {
                continue;
            }

            $categoryName = trim($row[$categoryIndex] ?? '');
            $categoryId = null;
            if ($categoryName !== '') {
                $category = $this->categoryModel->findByName($categoryName);
                if (!$category) {
                    $categoryId = $this->categoryModel->createCategory([
                        'name' => $categoryName,
                        'slug' => Helper::slugify($categoryName),
                        'status' => 'active'
                    ]);
                } else {
                    $categoryId = $category['id'];
                }
            }

            $data = [
                'name' => $name,
                'slug' => Helper::slugify($name),
                'price' => (float)($row[$priceIndex] ?? 0),
                'category_id' => $categoryId,
                'description' => $descriptionIndex !== false ? $row[$descriptionIndex] : null,
                'short_description' => $shortDescIndex !== false ? $row[$shortDescIndex] : null,
                'sku' => $skuIndex !== false ? $row[$skuIndex] : null,
                'stock_quantity' => $stockIndex !== false ? (int)$row[$stockIndex] : null,
                'status' => 'active'
            ];

            $this->productModel->createProduct($data);
            $imported++;
        }

        fclose($handle);
        $this->redirect('/admin/products?success=' . $imported . ' products imported');
    }

    /**
     * Download sample CSV template
     */
    public function downloadSampleCsv()
    {
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="products_import_template.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Create output stream
        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8 to ensure Excel displays correctly
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // CSV Headers - matching database structure and import expectations
        $headers = [
            'name',                    // Required - Product name
            'category',                // Required - Category name (will be matched or created)
            'price',                   // Required - Product price
            'compare_price',           // Optional - Compare/Original price
            'cost_price',              // Optional - Cost price
            'sku',                     // Optional - SKU code
            'short_description',      // Optional - Short description
            'description',             // Optional - Full description
            'stock_quantity',          // Optional - Stock quantity
            'stock_status',            // Optional - in_stock, out_of_stock, on_backorder
            'manage_stock',            // Optional - 1 or 0
            'is_featured',             // Optional - 1 or 0 (featured product)
            'status',                  // Optional - active, inactive, draft
            'sort_order',              // Optional - Display order
            'meta_title',              // Optional - SEO meta title
            'meta_description'        // Optional - SEO meta description
        ];

        // Write headers
        fputcsv($output, $headers);

        // Add sample data rows
        $sampleRows = [
            [
                'Jollof Rice',
                'Food',
                '15.99',
                '18.99',
                '8.00',
                'JOL-001',
                'Delicious Nigerian jollof rice with chicken',
                'Authentic Nigerian jollof rice cooked with tomatoes, peppers, and spices. Served with tender chicken pieces.',
                '50',
                'in_stock',
                '1',
                '1',
                'active',
                '1',
                'Jollof Rice - The Pembina Pint',
                'Order authentic Nigerian jollof rice online. Made fresh daily with premium ingredients.'
            ],
            [
                'Pepper Soup',
                'As E Dey Hot',
                '12.99',
                '',
                '6.00',
                'PEP-001',
                'Spicy Nigerian pepper soup',
                'Hot and spicy Nigerian pepper soup with your choice of meat or fish. Perfect for cold weather.',
                '30',
                'in_stock',
                '1',
                '0',
                'active',
                '2',
                'Pepper Soup - The Pembina Pint',
                'Traditional Nigerian pepper soup with authentic spices.'
            ],
            [
                'Maltina',
                'Drinks',
                '3.99',
                '',
                '2.00',
                'MAL-001',
                'Nigerian malt drink',
                'Refreshing Nigerian malt drink, non-alcoholic.',
                '100',
                'in_stock',
                '1',
                '0',
                'active',
                '1',
                'Maltina - The Pembina Pint',
                'Authentic Nigerian malt drink.'
            ]
        ];

        // Write sample rows
        foreach ($sampleRows as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}

