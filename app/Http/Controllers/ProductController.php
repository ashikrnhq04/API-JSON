<?php

namespace Http\Controllers;

use Models\Product;
use Views\JsonView;
use Core\Requests;
use Core\Mimic;

class ProductController {
    
    private Product $productModel;
    
    public function __construct() {
        $this->productModel = new Product();
    }
    
    /**
     * Get all products with optional category filtering
     */
    public function index(): void {
        try {
            $limit = $_GET['limit'] ?? 20; // Increased default to 20
            $offset = $_GET['offset'] ?? 0;
            $categoryId = $_GET['category_id'] ?? null;
            
            // Validate limit and offset
            $limit = min(max((int)$limit, 1), 100); // Between 1 and 100
            $offset = max((int)$offset, 0);
            
            if ($categoryId !== null) {
                $categoryId = (int)$categoryId;
                $products = $this->productModel->getByCategory($categoryId, $limit, $offset);
            } else {
                $products = $this->productModel->getAllWithCategories($limit, $offset);
            }
            
            // Add pagination info
            $totalProducts = $this->productModel->getTotalCount($categoryId);
            $pagination = [
                'total' => $totalProducts,
                'limit' => $limit,
                'offset' => $offset,
                'hasMore' => ($offset + $limit) < $totalProducts
            ];
            
            JsonView::successWithPagination($products, $pagination, 'Products retrieved successfully');
            
        } catch (Exception $e) {
            error_log("Error in ProductController::index: " . $e->getMessage());
            JsonView::error('Failed to retrieve products', 500);
        }
    }
    
    /**
     * Get a single product by ID
     */
    public function show(int $id): void {
        try {
            $product = $this->productModel->findBySlugOrId($id);
            
            if (!$product) {
                JsonView::notFound('No product found with the specified ID');
                return;
            }
            
            JsonView::success($product, 'Product retrieved successfully');
            
        } catch (Exception $e) {
            error_log("Error in ProductController::show: " . $e->getMessage());
            JsonView::error('Failed to retrieve product', 500);
        }
    }
    
    /**
     * Get a single product by URL slug
     */
    public function showBySlug(string $slug): void {
        try {
            $product = $this->productModel->findBySlugOrId($slug);
            
            if (!$product) {
                JsonView::notFound('No product found with the specified identifier');
                return;
            }
            
            JsonView::success($product, 'Product retrieved successfully');
            
        } catch (Exception $e) {
            error_log("Error in ProductController::showBySlug: " . $e->getMessage());
            JsonView::error('Failed to retrieve product', 500);
        }
    }
    
    /**
     * Create a new product
     */
    public function create(): void {
        try {
            $input = Requests::only([
                'title', 'description', 'price', 'image', 'categories'
            ]);

            if (!$input) {
                JsonView::validationError(['input' => 'Invalid JSON data']);
                return;
            }

            // Validate required fields
            Requests::validate([
                'title' => 'required|string|min:2|max:255',
                'description' => 'required|string|min:5|max:2000',
                'price' => 'required|number',
                'image' => 'required|url',
                'categories' => 'string',
            ]);

            if (Requests::fails()) {
                JsonView::validationError(Requests::errors(), 'Validation failed');
                return;
            }
            
            // Production environment mimic response
            if ($_ENV['APP_ENV'] === 'production') {
                $mockProduct = Mimic::generateMockProduct($input);
                JsonView::success($mockProduct, 'Product created successfully', 201);
                return;
            }
            
            // Create the product using the model
            $result = $this->productModel->create($input);
            
            if ($result['success']) {
                JsonView::success($result['product'], 'Product created successfully', 201);
            } else {
                JsonView::validationError($result['errors'], 'Product creation failed');
            }
            
        } catch (Exception $e) {
            error_log("Error in ProductController::create: " . $e->getMessage());
            JsonView::error('Failed to create product', 500);
        }
    }
    
    /**
     * Update an existing product by ID or slug (PATCH)
     */
    public function patchUpdate(string $identifier): void {
        try {
            $existingProduct = $this->productModel->findBySlugOrId($identifier);
            if (!$existingProduct) {
                JsonView::notFound('No product found with the specified identifier');
                return;
            }
            
            $input = Requests::only([
                'title', 'description', 'price', 'image', 'categories'
            ]);

            // Validate fields (optional for PATCH)
            Requests::validate([
                'title' => 'string|min:2|max:10',
                'description' => 'string|min:10',
                'price' => 'number',
                'image' => 'url',
                'categories' => 'string',
            ]);
            
            if (Requests::fails()) {
                JsonView::validationError(Requests::errors(), 'Validation failed');
                return;
            }

            // Production environment mimic response
            if ($_ENV['APP_ENV'] === 'production') {
                $mockProduct = Mimic::generateMockProduct(array_merge($existingProduct, $input));
                JsonView::success($mockProduct, 'Product updated successfully');
                return;
            }

            // Update the product using the model
            $result = $this->productModel->update($existingProduct['id'], $input);
            
            if ($result) {
                $updatedProduct = $this->productModel->findBySlugOrId($existingProduct['id']);
                JsonView::success($updatedProduct, 'Product updated successfully');
            } else {
                JsonView::error('Failed to update product', 500);
            }
            
        } catch (Exception $e) {
            error_log("Error in ProductController::patchUpdate: " . $e->getMessage());
            JsonView::error('Failed to update product', 500);
        }
    }

    /**
     * Update an existing product by ID or slug (PUT)
     */
    public function putUpdate(string $identifier): void {
        try {
            $existingProduct = $this->productModel->findBySlugOrId($identifier);
            if (!$existingProduct) {
                JsonView::notFound('No product found with the specified identifier');
                return;
            }
            
            $input = Requests::only([
                'title', 'description', 'price', 'image', 'categories'
            ]);

            // Validate required fields (all required for PUT)
            Requests::validate([
                'title' => 'required|string|min:2|max:255',
                'description' => 'required|string|min:5|max:2000',
                'price' => 'required|number',
                'image' => 'required|url',
                'categories' => 'string',
            ]);
            
            if (Requests::fails()) {
                JsonView::validationError(Requests::errors(), 'Validation failed');
                return;
            }

            // Production environment mimic response
            if ($_ENV['APP_ENV'] === 'production') {
                $mockProduct = Mimic::generateMockProduct($input);
                JsonView::success($mockProduct, 'Product updated successfully');
                return;
            }

            // Update the product using the model
            $result = $this->productModel->update($existingProduct['id'], $input);
            
            if ($result) {
                $updatedProduct = $this->productModel->findBySlugOrId($existingProduct['id']);
                JsonView::success($updatedProduct, 'Product updated successfully');
            } else {
                JsonView::error('Failed to update product', 500);
            }
            
        } catch (Exception $e) {
            error_log("Error in ProductController::putUpdate: " . $e->getMessage());
            JsonView::error('Failed to update product', 500);
        }
    }
    
    /**
     * Delete a product by ID or slug
     */
    public function destroyBySlug(string $identifier): void {
        try {
            $existingProduct = $this->productModel->findBySlugOrId($identifier);
            if (!$existingProduct) {
                JsonView::notFound('No product found with the specified identifier');
                return;
            }
            
            // Production environment mimic response
            if ($_ENV['APP_ENV'] === 'production') {
                JsonView::success(null, 'Product deleted successfully');
                return;
            }
            
            // Delete the product using the model
            $success = $this->productModel->delete($existingProduct['id']);
            
            if ($success) {
                JsonView::success(null, 'Product deleted successfully');
            } else {
                JsonView::error('Failed to delete product', 500);
            }
            
        } catch (Exception $e) {
            error_log("Error in ProductController::destroyBySlug: " . $e->getMessage());
            JsonView::error('Failed to delete product', 500);
        }
    }
}