<?php

namespace Http\Controllers;

use Models\Post;
use Views\JsonView;

class PostController {
    
    private Post $postModel;
    
    public function __construct() {
        $this->postModel = new Post();
    }
    
    /**
     * Get all posts with optional category filtering
     */
    public function index(): void {
        try {
            $limit = $_GET['limit'] ?? 50;
            $offset = $_GET['offset'] ?? 0;
            $categoryId = $_GET['category_id'] ?? null;
            
            // Validate limit and offset
            $limit = min(max((int)$limit, 1), 100); // Between 1 and 100
            $offset = max((int)$offset, 0);
            
            if ($categoryId !== null) {
                $categoryId = (int)$categoryId;
                $posts = $this->postModel->getByCategory($categoryId, $limit, $offset);
            } else {
                $posts = $this->postModel->getAllWithCategories($limit, $offset);
            }
            
            // Add pagination info
            $totalPosts = $this->postModel->getTotalCount($categoryId);
            $pagination = [
                'total' => $totalPosts,
                'limit' => $limit,
                'offset' => $offset,
                'hasMore' => ($offset + $limit) < $totalPosts
            ];
            
            JsonView::successWithPagination($posts, $pagination, 'Posts retrieved successfully');
            
        } catch (Exception $e) {
            error_log("Error in PostController::index: " . $e->getMessage());
            JsonView::error('Failed to retrieve posts', 500);
        }
    }
    
    /**
     * Get a single post by ID or URL slug
     */
    public function showBySlug(string $identifier): void {
        try {
            $post = $this->postModel->findBySlug($identifier);
            
            if (!$post) {
                JsonView::notFound('Post not found');
                return;
            }
            
            JsonView::success($post, 'Post retrieved successfully');
            
        } catch (Exception $e) {
            error_log("Error in PostController::showBySlug: " . $e->getMessage());
            JsonView::error('Failed to retrieve post', 500);
        }
    }
    
    /**
     * Create a new post
     */
    public function create(): void {
        try {
            // Parse JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                JsonView::validationError(['input' => 'Invalid JSON data']);
                return;
            }
            
            // Create the post using the model
            $result = $this->postModel->create($input);
            
            if ($result['success']) {
                JsonView::success($result['post'], 'Post created successfully', 201);
            } else {
                JsonView::validationError($result['errors'], 'Post creation failed');
            }
            
        } catch (Exception $e) {
            error_log("Error in PostController::create: " . $e->getMessage());
            JsonView::error('Failed to create post', 500);
        }
    }
    
    /**
     * Update an existing post by ID or slug
     */
    public function updateBySlug(string $identifier): void {
        try {
            // Check if post exists using slug method (handles both ID and slug)
            $existingPost = $this->postModel->findBySlug($identifier);
            if (!$existingPost) {
                JsonView::notFound('Post not found');
                return;
            }
            
            // Parse JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                JsonView::validationError(['input' => 'Invalid JSON data']);
                return;
            }
            
            // Update the post using the model (needs post ID)
            $result = $this->postModel->update($existingPost['id'], $input);
            
            if ($result) {
                // Get updated post
                $updatedPost = $this->postModel->findById($existingPost['id']);
                JsonView::success($updatedPost, 'Post updated successfully');
            } else {
                JsonView::error('Failed to update post', 500);
            }
            
        } catch (Exception $e) {
            error_log("Error in PostController::updateBySlug: " . $e->getMessage());
            JsonView::error('Failed to update post', 500);
        }
    }
    
    /**
     * Delete a post by ID or slug
     */
    public function destroyBySlug(string $identifier): void {
        try {
            // Check if post exists using slug method (handles both ID and slug)
            $existingPost = $this->postModel->findBySlug($identifier);
            if (!$existingPost) {
                JsonView::notFound('Post not found');
                return;
            }
            
            // Delete the post using the model (needs post ID)
            $success = $this->postModel->delete($existingPost['id']);
            
            if ($success) {
                JsonView::success(null, 'Post deleted successfully');
            } else {
                JsonView::error('Failed to delete post', 500);
            }
            
        } catch (Exception $e) {
            error_log("Error in PostController::destroyBySlug: " . $e->getMessage());
            JsonView::error('Failed to delete post', 500);
        }
    }
}
