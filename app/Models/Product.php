<?php

namespace Models;

use Core\Database;
use Core\App;

class Product {
    private Database $db;
    
    public function __construct() {
        $this->db = App::resolve(Database::class);
    }
    
    /**
     * Create a new product
     */
    public function create(array $data): array {
        // Business rules validation
        $this->validateProductData($data);
        
        // Prepare data for database
        $productData = [
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => (float) $data['price'],
            'image' => $data['image'],
            'url' => toSlug($data['title']),
            'status' => 'active'
        ];
        
        // Start transaction
        $transactionStarted = $this->db->beginTransaction();
        if (!$transactionStarted) {
            throw new \Exception("Failed to start database transaction");
        }
        
        try {
            // Insert product
            $this->db->insert('products', $productData);
            $productId = $this->db->lastInsertId();
            
            if (empty($productId)) {
                throw new \Exception("Failed to create product");
            }
            
            // Handle categories if provided
            if (!empty($data['category_ids'])) {
                $this->attachCategories($productId, $data['category_ids']);
            }
            
            $this->db->commit();
            $product = $this->findById($productId);
            
            return [
                'success' => true,
                'product' => $product
            ];
            
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
    
    /**
     * Find product by ID
     */
    public function findById(int $id): ?array {
        $sql = "SELECT p.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS categories 
                FROM products p
                LEFT JOIN product_category pc ON p.id = pc.product_id
                LEFT JOIN categories c ON c.id = pc.category_id 
                WHERE p.id = :id
                GROUP BY p.id LIMIT 1";

        $this->db->query($sql)->execute(['id' => $id]);
        $result = $this->db->fetch();
        
        if (!$result) {
            return null;
        }

        $result['categories'] = !empty($result['categories']) 
            ? array_map('trim', explode(',', $result['categories']))
            : [];

        return $result;
    }
    
    /**
     * Find product by slug
     */
    public function findBySlug(string $slug): ?array {
        $column = ctype_digit($slug) ? "id" : "url";

        $sql = "SELECT p.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS categories 
                FROM products p
                LEFT JOIN product_category pc ON p.id = pc.product_id
                LEFT JOIN categories c ON c.id = pc.category_id 
                WHERE p.`{$column}` = :{$column}
                GROUP BY p.id LIMIT 1";

        $this->db->query($sql)->execute([$column => $slug]);
        $result = $this->db->fetch();
        
        if (!$result) {
            return null;
        }

        $result['categories'] = !empty($result['categories']) 
            ? array_map('trim', explode(',', $result['categories']))
            : [];

        return $result;
    }
    
    /**
     * Get all products with categories
     */
    public function getAllWithCategories(int $limit = 50, int $offset = 0): array {
        $sql = "
            SELECT p.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS categories 
            FROM products p
            LEFT JOIN product_category pc ON p.id = pc.product_id
            LEFT JOIN categories c ON c.id = pc.category_id 
            WHERE p.status = 'active'
            GROUP BY p.id 
            ORDER BY p.id ASC
            LIMIT {$limit} OFFSET {$offset}
        ";
        
        $this->db->query($sql)->execute([]);
        $products = $this->db->fetchAll();
        
        // Process categories for each product
        foreach ($products as &$product) {
            $product['categories'] = !empty($product['categories']) 
                ? array_map('trim', explode(',', $product['categories']))
                : [];
        }
        
        return $products;
    }
    
    /**
     * Get total count of products
     */
    public function getTotalCount(?int $categoryId = null): int {
        if ($categoryId !== null) {
            $sql = "
                SELECT COUNT(DISTINCT p.id) as count
                FROM products p
                LEFT JOIN product_category pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id
                WHERE c.id = :category_id AND p.status = 'active'
            ";
            
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
        $this->validateProductData($data, false); // false = not all fields required for update
        
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
        
        if (empty($updateData)) {
            return true; // Nothing to update
        }
        
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        // Build update SQL query
        $setClause = implode(", ", array_map(fn($key) => "`{$key}` = :{$key}", array_keys($updateData)));
        $sql = "UPDATE `products` SET {$setClause} WHERE `id` = :id";
        
        // Add the ID to the parameters
        $updateData['id'] = $id;
        
        $this->db->query($sql)->execute($updateData);
        
        return true; // Assume success if no exception thrown
    }
    
    /**
     * Get products by category with pagination
     */
    public function getByCategory(int $categoryId, int $limit = 50, int $offset = 0): array {
        try {
            $sql = "
                SELECT p.*, GROUP_CONCAT(c2.name SEPARATOR ', ') AS categories
                FROM products p
                LEFT JOIN product_category pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id
                LEFT JOIN product_category pc2 ON p.id = pc2.product_id
                LEFT JOIN categories c2 ON pc2.category_id = c2.id
                WHERE c.id = :category_id AND p.status = 'active'
                GROUP BY p.id
                ORDER BY p.created_at DESC
                LIMIT {$limit} OFFSET {$offset}
            ";
            
            $this->db->query($sql)->execute([
                'category_id' => $categoryId
            ]);
            
            $products = $this->db->fetchAll();
            
            // Process categories for each product
            foreach ($products as &$product) {
                $product['categories'] = !empty($product['categories']) 
                    ? array_map('trim', explode(',', $product['categories']))
                    : [];
            }
            
            return $products;
            
        } catch (Exception $e) {
            error_log("Error in Product::getByCategory: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a product and its relationships
     */
    public function delete(int $id): bool {
        // Start transaction to ensure data consistency
        $transactionStarted = $this->db->beginTransaction();
        if (!$transactionStarted) {
            throw new \Exception("Failed to start database transaction");
        }
        
        try {
            // First delete category relationships
            $deleteCategoriesSql = "DELETE FROM `product_category` WHERE `product_id` = :product_id";
            $this->db->query($deleteCategoriesSql)->execute(['product_id' => $id]);
            
            // Then delete the product
            $deleteProductSql = "DELETE FROM `products` WHERE `id` = :id";
            $this->db->query($deleteProductSql)->execute(['id' => $id]);
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
    
    /**
     * Business logic validation
     */
    private function validateProductData(array $data, bool $allRequired = true): void {
        if ($allRequired) {
            if (empty($data['title'])) {
                throw new \InvalidArgumentException("Product title is required");
            }
            if (empty($data['description'])) {
                throw new \InvalidArgumentException("Product description is required");
            }
            if (!isset($data['price']) || !is_numeric($data['price'])) {
                throw new \InvalidArgumentException("Valid product price is required");
            }
            if (empty($data['image'])) {
                throw new \InvalidArgumentException("Product image is required");
            }
        }
        
        // Validate individual fields if provided
        if (isset($data['price']) && (!is_numeric($data['price']) || $data['price'] < 0)) {
            throw new \InvalidArgumentException("Product price must be a positive number");
        }
        
        if (isset($data['image']) && !filter_var($data['image'], FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Product image must be a valid URL");
        }
        
        if (isset($data['title']) && strlen(trim($data['title'])) < 2) {
            throw new \InvalidArgumentException("Product title must be at least 2 characters");
        }
        
        if (isset($data['description']) && strlen(trim($data['description'])) < 5) {
            throw new \InvalidArgumentException("Product description must be at least 5 characters");
        }
    }
    
    /**
     * Handle category attachment (business logic)
     */
    private function attachCategories(int $productId, $categories): void {
        if (empty($categories)) {
            return;
        }
        
        // Handle both array of IDs and comma-separated string of names
        if (is_string($categories)) {
            // Legacy: comma-separated category names
            $categoryNames = array_filter(array_map('trim', explode(',', $categories)));
            foreach ($categoryNames as $categoryName) {
                if (empty($categoryName)) {
                    continue;
                }
                $categoryId = $this->getOrCreateCategory($categoryName);
                $this->linkProductToCategory($productId, $categoryId);
            }
        } elseif (is_array($categories)) {
            // New: array of category IDs
            foreach ($categories as $categoryId) {
                $this->linkProductToCategory($productId, (int)$categoryId);
            }
        }
    }
    
    /**
     * Link product to category (avoid duplicates)
     */
    private function linkProductToCategory(int $productId, int $categoryId): void {
        // Check if the relationship already exists
        $existing = $this->db->select("product_category", ["product_id"], [
            "product_id" => $productId,
            "category_id" => $categoryId
        ]);

        if (!empty($existing)) {
            return;
        }

        // Only insert if the relationship doesn't exist
        $this->db->insert("product_category", [
            "product_id" => $productId,
            "category_id" => $categoryId
        ]);
    }
    
    /**
     * Get or create category
     */
    private function getOrCreateCategory(string $name): int {
        $categoryName = strtolower(trim($name));
        
        // Use select method to search by name
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
}