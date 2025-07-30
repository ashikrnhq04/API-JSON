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
        // Pick only necessary fields
        $postData = array_intersect_key($data, array_flip([
            "title", "content", "image"
        ]));

        $postData['url'] = toSlug($postData['title']);
        $postData['status'] = 'active';
        
        $this->db->beginTransaction();
        
        try {
            $this->db->insert('posts', $postData);
            $postId = $this->db->lastInsertId();
            
            if (empty($postId)) {
                throw new \Exception("Failed to create post");
            }
            
            // Handle categories if provided
            if (!empty($data['categories'])) {
                $this->attachCategories($postId, $data['categories']);
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'post' => $this->findById($postId)
            ];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Find post by ID
     */
    public function findById(int $id): ?array {
        $post = $this->db->find('posts', (string)$id);
        
        if (!$post) {
            return null;
        }
        
        $post['categories'] = $this->getPostCategories($id);
        return $post;
    }
    
    /**
     * Find post by slug
     */
    public function findBySlug(string $slug): ?array {
        $post = $this->db->find('posts', $slug);
        
        if (!$post) {
            return null;
        }
        
        $post['categories'] = $this->getPostCategories($post['id']);
        return $post;
    }
    
    /**
     * Get all posts with categories
     */
    public function getAllWithCategories(int $limit = 50, int $offset = 0): array {
        $sql = "SELECT * FROM posts WHERE status = 'active' ORDER BY id ASC LIMIT {$limit} OFFSET {$offset}";
        
        $this->db->query($sql)->execute([]);
        $posts = $this->db->fetchAll();
        
        return $this->attachCategoriesToPosts($posts);
    }
    
    /**
     * Get total count of posts
     */
    public function getTotalCount(?int $categoryId = null): int {
        if ($categoryId !== null) {
            $sql = "SELECT COUNT(DISTINCT p.id) as count FROM posts p 
                    INNER JOIN post_category pc ON p.id = pc.post_id 
                    WHERE p.status = 'active' AND pc.category_id = :category_id";
            $this->db->query($sql)->execute(['category_id' => $categoryId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM posts WHERE status = 'active'";
            $this->db->query($sql)->execute([]);
        }
        
        $result = $this->db->fetch();
        return (int) $result['count'];
    }

    /**
     * Update post
     */
    public function update(int $id, array $data): bool {
        $updateData = $this->prepareUpdateData($data);
        $hasCategories = array_key_exists('categories', $data);
        
        // Use transaction only if updating categories
        if ($hasCategories) {
            $this->db->beginTransaction();
        }
        
        try {
            // Update post fields if any
            if (!empty($updateData)) {
                $updateData['updated_at'] = date('Y-m-d H:i:s');
                $updateData['id'] = $id;
                
                $setClause = implode(", ", array_map(fn($key) => "`{$key}` = :{$key}", array_keys($updateData)));
                $sql = "UPDATE posts SET {$setClause} WHERE id = :id";
                
                $this->db->query($sql)->execute($updateData);
            }
            
            // Handle category updates if provided
            if ($hasCategories) {
                $this->updatePostCategories($id, $data['categories']);
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
     * Get posts by category
     */
    public function getByCategory(int $categoryId, int $limit = 50, int $offset = 0): array {
        $sql = "SELECT p.* FROM posts p 
                INNER JOIN post_category pc ON p.id = pc.post_id 
                WHERE p.status = 'active' AND pc.category_id = :category_id 
                ORDER BY p.id ASC LIMIT {$limit} OFFSET {$offset}";
        
        $this->db->query($sql)->execute(['category_id' => $categoryId]);
        $posts = $this->db->fetchAll();
        
        return $this->attachCategoriesToPosts($posts);
    }

    /**
     * Delete post
     */
    public function delete(int $id): bool {
        $this->db->beginTransaction();
        
        try {
            $this->db->query("DELETE FROM post_category WHERE post_id = :id")->execute(['id' => $id]);
            $this->db->query("DELETE FROM posts WHERE id = :id")->execute(['id' => $id]);
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    // ========== PRIVATE HELPER METHODS ==========
    
    /**
     * Get categories for a specific post
     */
    private function getPostCategories(int $postId): array {
        $sql = "SELECT c.name FROM categories c 
                INNER JOIN post_category pc ON c.id = pc.category_id 
                WHERE pc.post_id = :post_id ORDER BY c.name";
        
        $this->db->query($sql)->execute(['post_id' => $postId]);
        $categories = $this->db->fetchAll();
        
        return array_column($categories, 'name');
    }
    
    /**
     * Attach categories to multiple posts efficiently
     */
    private function attachCategoriesToPosts(array $posts): array {
        if (empty($posts)) {
            return $posts;
        }
        
        $postIds = array_column($posts, 'id');
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));
        
        $sql = "SELECT pc.post_id, c.name 
                FROM post_category pc 
                INNER JOIN categories c ON pc.category_id = c.id 
                WHERE pc.post_id IN ({$placeholders})
                ORDER BY c.name";
        
        $this->db->query($sql)->execute($postIds);
        $categoryData = $this->db->fetchAll();
        
        // Group categories by post_id
        $categoriesByPost = [];
        foreach ($categoryData as $row) {
            $categoriesByPost[$row['post_id']][] = $row['name'];
        }
        
        // Attach categories to posts
        foreach ($posts as &$post) {
            $post['categories'] = $categoriesByPost[$post['id']] ?? [];
        }
        
        return $posts;
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
        if (isset($data['content'])) {
            $updateData['content'] = $data['content'];
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
    private function attachCategories(int $postId, $categories): void {
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
                $this->linkPostToCategory($postId, $categoryId);
            }
        }
    }
    
    /**
     * Link post to category (avoid duplicates)
     */
    private function linkPostToCategory(int $postId, int $categoryId): void {
        $existing = $this->db->select("post_category", ["post_id"], [
            "post_id" => $postId,
            "category_id" => $categoryId
        ]);
        
        if (empty($existing)) {
            $this->db->insert("post_category", [
                "post_id" => $postId,
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
     * Update post categories
     */
    private function updatePostCategories(int $postId, $categories): void {
        // Remove existing associations
        $this->db->query("DELETE FROM post_category WHERE post_id = :post_id")
                 ->execute(['post_id' => $postId]);
        
        // Add new associations
        if (!empty($categories)) {
            $this->attachCategories($postId, $categories);
        }
    }
}