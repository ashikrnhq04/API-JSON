<?php

namespace Models;

use Core\Database;
use Core\App;
use Core\Validator;

class Product {
    private Database $db;
    
    public function __construct() {
        $this->db = App::resolve(Database::class);
    }
    
    /**
     * Create a new product
     */
    public function create(array $data): array {
        // Pick only necessary fields
        $productData = array_intersect_key($data, array_flip([
            "title", "description", "price", "image"
        ]));

        $productData['url'] = toSlug($productData['title']);
        $productData['status'] = 'active';
        
        $this->db->beginTransaction();
        
        try {
            $this->db->insert('products', $productData);
            $productId = $this->db->lastInsertId();
            
            if (empty($productId)) {
                throw new \Exception("Failed to create product");
            }
            
            // Handle categories if provided
            if (!empty($data['categories'])) {
                $this->attachCategories($productId, $data['categories']);
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'product' => $this->findById($productId)
            ];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Find product by ID
     */
    public function findById(int $id): ?array {
        $product = $this->db->find('products', (string)$id);
        
        if (!$product) {
            return null;
        }
        
        $product['categories'] = $this->getProductCategories($id);
        return $product;
    }
    
    /**
     * Find product by slug
     */
    public function findBySlug(string $slug): ?array {
        $product = $this->db->find('products', $slug);
        
        if (!$product) {
            return null;
        }
        
        $product['categories'] = $this->getProductCategories($product['id']);
        return $product;
    }
    
    /**
     * Get all products with categories
     */
    public function getAllWithCategories(int $limit = 50, int $offset = 0): array {
        $sql = "SELECT * FROM products WHERE status = 'active' ORDER BY id ASC LIMIT {$limit} OFFSET {$offset}";
        
        $this->db->query($sql)->execute([]);
        $products = $this->db->fetchAll();
        
        return $this->attachCategoriesToProducts($products);
    }
    
    /**
     * Get total count of products
     */
    public function getTotalCount(?int $categoryId = null): int {
        if ($categoryId !== null) {
            $sql = "SELECT COUNT(DISTINCT p.id) as count FROM products p 
                    INNER JOIN product_category pc ON p.id = pc.product_id 
                    WHERE p.status = 'active' AND pc.category_id = :category_id";
            $this->db->query($sql)->execute(['category_id' => $categoryId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM products WHERE status = 'active'";
            $this->db->query($sql)->execute([]);
        }
        
        $result = $this->db->fetch();
        return (int) $result['count'];
    }

    /**
     * Update product
     */
    public function update(int $id, array $data): bool {
        $updateData = $this->prepareUpdateData($data);
        $hasCategories = array_key_exists('categories', $data);
        
        // Use transaction only if updating categories
        if ($hasCategories) {
            $this->db->beginTransaction();
        }
        
        try {
            // Update product fields if any
            if (!empty($updateData)) {
                $updateData['updated_at'] = date('Y-m-d H:i:s');
                $updateData['id'] = $id;
                
                $setClause = implode(", ", array_map(fn($key) => "`{$key}` = :{$key}", array_keys($updateData)));
                $sql = "UPDATE products SET {$setClause} WHERE id = :id";
                
                $this->db->query($sql)->execute($updateData);
            }
            
            // Handle category updates if provided
            if ($hasCategories) {
                $this->updateProductCategories($id, $data['categories']);
                $this->db->commit();
            }
            
            return true;
            
        } catch (\Exception $e) {
            if ($hasCategories) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
    
    /**
     * Get products by category
     */
    public function getByCategory(int $categoryId, int $limit = 50, int $offset = 0): array {
        $sql = "SELECT p.* FROM products p 
                INNER JOIN product_category pc ON p.id = pc.product_id 
                WHERE p.status = 'active' AND pc.category_id = :category_id 
                ORDER BY p.id ASC LIMIT {$limit} OFFSET {$offset}";
        
        $this->db->query($sql)->execute(['category_id' => $categoryId]);
        $products = $this->db->fetchAll();
        
        return $this->attachCategoriesToProducts($products);
    }

    /**
     * Delete product
     */
    public function delete(int $id): bool {
        $this->db->beginTransaction();
        
        try {
            $this->db->query("DELETE FROM product_category WHERE product_id = :id")->execute(['id' => $id]);
            $this->db->query("DELETE FROM products WHERE id = :id")->execute(['id' => $id]);
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    // ========== PRIVATE HELPER METHODS ==========
    
    /**
     * Get categories for a specific product
     */
    private function getProductCategories(int $productId): array {
        $sql = "SELECT c.name FROM categories c 
                INNER JOIN product_category pc ON c.id = pc.category_id 
                WHERE pc.product_id = :product_id ORDER BY c.name";
        
        $this->db->query($sql)->execute(['product_id' => $productId]);
        $categories = $this->db->fetchAll();
        
        return array_column($categories, 'name');
    }
    
    /**
     * Attach categories to multiple products efficiently
     */
    private function attachCategoriesToProducts(array $products): array {
        if (empty($products)) {
            return $products;
        }
        
        $productIds = array_column($products, 'id');
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        
        $sql = "SELECT pc.product_id, c.name 
                FROM product_category pc 
                INNER JOIN categories c ON pc.category_id = c.id 
                WHERE pc.product_id IN ({$placeholders})
                ORDER BY c.name";
        
        $this->db->query($sql)->execute($productIds);
        $categoryData = $this->db->fetchAll();
        
        // Group categories by product_id
        $categoriesByProduct = [];
        foreach ($categoryData as $row) {
            $categoriesByProduct[$row['product_id']][] = $row['name'];
        }
        
        // Attach categories to products
        foreach ($products as &$product) {
            $product['categories'] = $categoriesByProduct[$product['id']] ?? [];
        }
        
        return $products;
    }
    
    /**
     * Prepare update data from input
     */
    private function prepareUpdateData(array $data): array {
        $updateData = [];
        
        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
            $updateData['url'] = toSlug($data['title']);
        }
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }
        if (isset($data['price'])) {
            $updateData['price'] = (float) $data['price'];
        }
        if (isset($data['image'])) {
            $updateData['image'] = $data['image'];
        }
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }
        
        return $updateData;
    }
    
    /**
     * Handle category attachment 
     */
    private function attachCategories(int $productId, $categories): void {
        if (empty($categories) || (is_string($categories) && trim($categories) === '')) {
            return;
        }
        
        $categoryNames = is_string($categories) 
            ? array_filter(array_map('trim', explode(',', $categories)))
            : array_filter($categories);
        
        foreach ($categoryNames as $categoryName) {
            $categoryName = trim($categoryName);
            if (!empty($categoryName)) {
                $categoryId = $this->getOrCreateCategory($categoryName);
                $this->linkProductToCategory($productId, $categoryId);
            }
        }
    }
    
    /**
     * Link product to category (avoid duplicates)
     */
    private function linkProductToCategory(int $productId, int $categoryId): void {
        $existing = $this->db->select("product_category", ["product_id"], [
            "product_id" => $productId,
            "category_id" => $categoryId
        ]);
        
        if (empty($existing)) {
            $this->db->insert("product_category", [
                "product_id" => $productId,
                "category_id" => $categoryId
            ]);
        }
    }
    
    /**
     * Get or create category
     */
    private function getOrCreateCategory(string $name): int {
        $categoryName = strtolower(trim($name));
        
        $existing = $this->db->select("categories", ["id"], ["name" => $categoryName]);
        
        if (!empty($existing)) {
            return (int) $existing[0]["id"];
        }
        
        $this->db->insert("categories", [
            "name" => $categoryName, 
            "url" => toSlug($categoryName)
        ]);
        
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Update product categories
     */
    private function updateProductCategories(int $productId, $categories): void {
        // Remove existing associations
        $this->db->query("DELETE FROM product_category WHERE product_id = :product_id")
                 ->execute(['product_id' => $productId]);
        
        // Add new associations
        if (!empty($categories)) {
            $this->attachCategories($productId, $categories);
        }
    }

    public function validate(array $data): array {
        $validator = new Validator($data, [
            'title' => 'required|string|min:2|max:255',
            'description' => 'required|string|min:5|max:1000',
            'price' => 'required|number',
            'image' => 'required|url'
        ]);
        
        if (!$validator->passes()) {
            return $validator->errors();
        }
        
        return [];
    }
}