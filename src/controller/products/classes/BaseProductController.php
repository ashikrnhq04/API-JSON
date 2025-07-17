<?php

use src\Core\App; 
use src\Core\Database; 
use src\Core\SchemaManager;
use src\Core\Requests;

abstract class BaseProductController {
    protected Database $db;
    protected array $error = [];
    protected array $data = [];

    public function __construct() {
        try {
            $this->db = App::resolve(Database::class);
        } catch(\Exception $e) {
            abort(500, [
                "message" => "Database connection failed",
                "serverError" => $e
            ]);
        }
    }

    protected function getProductBySlug(string $slug): array {
        $column = ctype_digit($slug) ? "id" : "url";

        $sql = "SELECT p.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS categories 
                FROM products p
                LEFT JOIN product_category pc ON p.id = pc.product_id
                LEFT JOIN categories c ON c.id = pc.category_id 
                WHERE p.`{$column}` = :{$column}
                GROUP BY p.id LIMIT 1";

        $result = $this->db->query($sql)->execute([$column => $slug])->fetch();
        
        if (!$result) {
            return [];
        }

        $result['categories'] = !empty($result['categories']) 
            ? array_map('trim', explode(',', $result['categories']))
            : [];

        return $result;
    }

    protected function handleCategoryOperations(int $productId, array $categories): void {
        
        if (empty($categories)) {
            return;
        }

        foreach ($categories as $index => $categoryName) {
            $categoryName = trim($categoryName);
            
            if (empty($categoryName)) {
                continue;
            }

            try {
                $categoryId = $this->getOrCreateCategory($categoryName);
                
                $this->linkProductToCategory($productId, $categoryId);
            } catch (\Exception $e) {
                 abort(500, [
                    "message" => "Failed to process category '$categoryName'",
                    "serverError" => $e
                ]);
            }
        }
        
    }

    protected function getOrCreateCategory(string $categoryName): int {
        
        // Use select method instead of find to search by name
        $existingCategory = $this->db->select("categories", ["id"], ["name" => $categoryName]);

        if (!empty($existingCategory)) {

            return (int) $existingCategory[0]["id"];
        }

        $this->db->insert("categories", [
            "name" => $categoryName, 
            "url" => toSlug($categoryName)
        ]);

        return (int) $this->db->lastInsertId();
    }

    protected function linkProductToCategory(int $productId, int $categoryId): void {
        
        // Check if the relationship already exists
        $existing = $this->db->select("product_category", ["product_id"], [
            "product_id" => $productId,
            "category_id" => $categoryId
        ]);

        if (!empty($existing)) {
            return;
        }

        // Only insert if the relationship doesn't exist
        try {
            $this->db->insert("product_category", [
                "product_id" => $productId,
                "category_id" => $categoryId
            ]);
        } catch (\Exception $e) {
            abort(500, [
                "message" => "Failed to create link between product and category",
                "serverError" => $e
            ]);
        }
    }

    protected function handleTableOperations(): void { 
        
        try {
            if(!$this->db->hasTable("products")) {
                $this->db->createTable("products", SchemaManager::get("products"));
            }

            if(!$this->db->hasTable("categories")) {
                $this->db->createTable("categories", SchemaManager::get("categories"));
            }

            if(!$this->db->hasTable("product_category")) {
                $this->db->createTable("product_category", SchemaManager::get("product_category"));
            }

        } catch(\Exception $e) {
            abort(500, [
                "message" => "Database connection failed",
                "serverError" => $e
            ]);
        }
    }
}