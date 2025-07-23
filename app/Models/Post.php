<?php

namespace Models;

use Core\Database;
use Core\App;

class Post {
    private Database $db;
    
    public function __construct() {
        $this->db = App::resolve(Database::class);
    }
    
    /**
     * Create a new post
     */
    public function create(array $data): array {
        // Business rules validation
        $this->validatePostData($data);
        
                // Prepare data for database
        $postData = [
            'title' => $data['title'],
            'content' => $data['content'],
            'image' => $data['image'],
            'url' => toSlug($data['title'])
        ];
        
        // Start transaction
        $transactionStarted = $this->db->beginTransaction();
        if (!$transactionStarted) {
            throw new \Exception("Failed to start database transaction");
        }
        
        try {
            // Insert post
            $this->db->insert('posts', $postData);
            $postId = $this->db->lastInsertId();
            
            if (empty($postId)) {
                throw new \Exception("Failed to create post");
            }
            
            // Handle categories if provided
            if (!empty($data['category_ids'])) {
                $this->attachCategories($postId, $data['category_ids']);
            }
            
            $this->db->commit();
            $post = $this->findById($postId);
            
            return [
                'success' => true,
                'post' => $post
            ];
            
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
    
    /**
     * Find post by ID
     */
    public function findById(int $id): ?array {
        $sql = "SELECT p.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS categories 
                FROM posts p
                LEFT JOIN post_category pc ON p.id = pc.post_id
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
     * Find post by slug
     */
    public function findBySlug(string $slug): ?array {
        $column = ctype_digit($slug) ? "id" : "url";

        $sql = "SELECT p.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS categories 
                FROM posts p
                LEFT JOIN post_category pc ON p.id = pc.post_id
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
     * Get all posts with categories
     */
    public function getAllWithCategories(int $limit = 50, int $offset = 0): array {
        $sql = "
            SELECT p.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS categories 
            FROM posts p
            LEFT JOIN post_category pc ON p.id = pc.post_id
            LEFT JOIN categories c ON c.id = pc.category_id 
            GROUP BY p.id 
            ORDER BY p.id ASC
            LIMIT {$limit} OFFSET {$offset}
        ";
        
        $this->db->query($sql)->execute([]);
        $posts = $this->db->fetchAll();
        
        // Process categories for each post
        foreach ($posts as &$post) {
            $post['categories'] = !empty($post['categories']) 
                ? array_map('trim', explode(',', $post['categories']))
                : [];
        }
        
        return $posts;
    }
    
    /**
     * Get total count of posts
     */
    public function getTotalCount(?int $categoryId = null): int {
        if ($categoryId !== null) {
            $sql = "
                SELECT COUNT(DISTINCT p.id) as count
                FROM posts p
                LEFT JOIN post_category pc ON p.id = pc.post_id
                LEFT JOIN categories c ON pc.category_id = c.id
                WHERE c.id = :category_id
            ";
            
            $this->db->query($sql)->execute(['category_id' => $categoryId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM posts";
            $this->db->query($sql)->execute([]);
        }
        
        $result = $this->db->fetch();
        return (int) $result['count'];
    }

    /**
     * Update post
     */
    public function update(int $id, array $data): bool {
        $this->validatePostData($data, false); // false = not all fields required for update
        
        $updateData = [];
        
        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
            $updateData['url'] = toSlug($data['title']);
        }
        if (isset($data['content'])) {
            $updateData['content'] = $data['content'];
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
        $sql = "UPDATE `posts` SET {$setClause} WHERE `id` = :id";
        
        // Add the ID to the parameters
        $updateData['id'] = $id;
        
        $this->db->query($sql)->execute($updateData);
        
        return true; // Assume success if no exception thrown
    }
    
    /**
     * Get posts by category with pagination
     */
    public function getByCategory(int $categoryId, int $limit = 50, int $offset = 0): array {
        try {
            $sql = "
                SELECT p.*, GROUP_CONCAT(c2.name SEPARATOR ', ') AS categories
                FROM posts p
                LEFT JOIN post_category pc ON p.id = pc.post_id
                LEFT JOIN categories c ON pc.category_id = c.id
                LEFT JOIN post_category pc2 ON p.id = pc2.post_id
                LEFT JOIN categories c2 ON pc2.category_id = c2.id
                WHERE c.id = :category_id
                GROUP BY p.id
                ORDER BY p.created_at DESC
                LIMIT {$limit} OFFSET {$offset}
            ";
            
            $this->db->query($sql)->execute([
                'category_id' => $categoryId
            ]);
            
            $posts = $this->db->fetchAll();
            
            // Process categories for each post
            foreach ($posts as &$post) {
                $post['categories'] = !empty($post['categories']) 
                    ? array_map('trim', explode(',', $post['categories']))
                    : [];
            }
            
            return $posts;
            
        } catch (Exception $e) {
            error_log("Error in Post::getByCategory: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a post and its relationships
     */
    public function delete(int $id): bool {
        // Start transaction to ensure data consistency
        $transactionStarted = $this->db->beginTransaction();
        if (!$transactionStarted) {
            throw new \Exception("Failed to start database transaction");
        }
        
        try {
            // First delete category relationships
            $deleteCategoriesSql = "DELETE FROM `post_category` WHERE `post_id` = :post_id";
            $this->db->query($deleteCategoriesSql)->execute(['post_id' => $id]);
            
            // Then delete the post
            $deletePostSql = "DELETE FROM `posts` WHERE `id` = :id";
            $this->db->query($deletePostSql)->execute(['id' => $id]);
            
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
    private function validatePostData(array $data, bool $allRequired = true): void {
        if ($allRequired) {
            if (empty($data['title'])) {
                throw new \InvalidArgumentException("Post title is required");
            }
            if (empty($data['content'])) {
                throw new \InvalidArgumentException("Post content is required");
            }
            if (empty($data['image'])) {
                throw new \InvalidArgumentException("Post image is required");
            }
        }
        
        // Validate individual fields if provided
        if (isset($data['image']) && !filter_var($data['image'], FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Post image must be a valid URL");
        }
        
        if (isset($data['title']) && strlen(trim($data['title'])) < 2) {
            throw new \InvalidArgumentException("Post title must be at least 2 characters");
        }
        
        if (isset($data['content']) && strlen(trim($data['content'])) < 10) {
            throw new \InvalidArgumentException("Post content must be at least 10 characters");
        }
    }
    
    /**
     * Handle category attachment (business logic)
     */
    private function attachCategories(int $postId, $categories): void {
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
                $this->linkPostToCategory($postId, $categoryId);
            }
        } elseif (is_array($categories)) {
            // New: array of category IDs
            foreach ($categories as $categoryId) {
                $this->linkPostToCategory($postId, (int)$categoryId);
            }
        }
    }
    
    /**
     * Link post to category (avoid duplicates)
     */
    private function linkPostToCategory(int $postId, int $categoryId): void {
        // Check if the relationship already exists
        $existing = $this->db->select("post_category", ["post_id"], [
            "post_id" => $postId,
            "category_id" => $categoryId
        ]);

        if (!empty($existing)) {
            return;
        }

        // Only insert if the relationship doesn't exist
        $this->db->insert("post_category", [
            "post_id" => $postId,
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
