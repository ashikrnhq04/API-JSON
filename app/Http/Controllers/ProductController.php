<?php

namespace Http\Controllers;

use Models\Product;
use Views\JsonView;

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
            $limit = $_GET['limit'] ?? 50; // Increased default to 50
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
            $product = $this->productModel->findById($id);
            
            if (!$product) {
                JsonView::notFound('Product not found');
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
            $product = $this->productModel->findBySlug($slug);
            
            if (!$product) {
                JsonView::notFound('Product not found');
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
            // Parse JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                JsonView::validationError(['input' => 'Invalid JSON data']);
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
     * Update an existing product by ID or slug
     */
    public function updateBySlug(string $identifier): void {
        try {
            // Check if product exists using slug method (handles both ID and slug)
            $existingProduct = $this->productModel->findBySlug($identifier);
            if (!$existingProduct) {
                JsonView::notFound('Product not found');
                return;
            }
            
            // Parse JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                JsonView::validationError(['input' => 'Invalid JSON data']);
                return;
            }
            
            // Update the product using the model (needs product ID)
            $result = $this->productModel->update($existingProduct['id'], $input);
            
            if ($result) {
                // Get updated product
                $updatedProduct = $this->productModel->findById($existingProduct['id']);
                JsonView::success($updatedProduct, 'Product updated successfully');
            } else {
                JsonView::error('Failed to update product', 500);
            }
            
        } catch (Exception $e) {
            error_log("Error in ProductController::updateBySlug: " . $e->getMessage());
            JsonView::error('Failed to update product', 500);
        }
    }
    
    /**
     * Delete a product by ID or slug
     */
    public function destroyBySlug(string $identifier): void {
        try {
            // Check if product exists using slug method (handles both ID and slug)
            $existingProduct = $this->productModel->findBySlug($identifier);
            if (!$existingProduct) {
                JsonView::notFound('Product not found');
                return;
            }
            
            // Delete the product using the model (needs product ID)
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