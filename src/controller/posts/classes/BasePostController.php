<?php

use src\Core\App; 
use src\Core\Database; 
use src\Core\SchemaManager;
use src\Core\Requests;

abstract class BasePostController {
    protected Database $db;
    protected array $error = [];
    protected array $data = [];

    public function __construct() {
        try {
            $this->db = App::resolve(Database::class);
        } catch(\Exception $e) {
            abort(500, [
                "message" => "Database connection failed",
                "error" => $e
            ]);
        }
    }

    protected function getPostBySlug(string $slug): array {
        $column = ctype_digit($slug) ? "id" : "url";

        $sql = "SELECT p.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS categories 
                FROM posts p
                LEFT JOIN post_category pc ON p.id = pc.post_id
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

    protected function handleCategoryOperations(int $postId, array $categories): void {
        if (empty($categories)) return;


        foreach ($categories as $categoryName) {
            $categoryName = trim($categoryName);
            if (empty($categoryName)) continue;

            $categoryId = $this->getOrCreateCategory($categoryName);
            $this->linkPostToCategory($postId, $categoryId);
        }
    }

    protected function getOrCreateCategory(string $categoryName): int {
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

    protected function linkPostToCategory(int $postId, int $categoryId): void {
        // Check if the relationship already exists
        $existing = $this->db->select("post_category", ["post_id"], [
            "post_id" => $postId,
            "category_id" => $categoryId
        ]);

        // Only insert if the relationship doesn't exist
        if (empty($existing)) {
            $this->db->insert("post_category", [
                "post_id" => $postId,
                "category_id" => $categoryId
            ]);
        }
    }

    protected function handleTableOperations(): void { 
        
        try {
            if(!$this->db->hasTable("posts")) {
                $this->db->createTable("posts", SchemaManager::get("posts"));
            }

            if(!$this->db->hasTable("categories")) {
                $this->db->createTable("categories", SchemaManager::get("categories"));
            }

            if(!$this->db->hasTable("post_category")) {
                $this->db->createTable("post_category", SchemaManager::get("post_category"));
            }

        } catch(\Exception $e) {
            abort(500, [
                "message" => "Database connection failed",
                "serverError" => $e
            ]);
        }
    }
}