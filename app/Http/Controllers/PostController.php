<?php

namespace Http\Controllers;

use Models\Post;
use Views\JsonView;
use Core\Requests;
use Core\Mimic;

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
            $limit = $_GET['limit'] ?? 20; // Increased default to 20
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
     * Get a single post by ID
     */
    public function show(int $id): void {
        try {
            $post = $this->postModel->findBySlugOrId($id);
            
            if (!$post) {
                JsonView::notFound('No post found with the specified ID');
                return;
            }
            
            JsonView::success($post, 'Post retrieved successfully');
            
        } catch (Exception $e) {
            error_log("Error in PostController::show: " . $e->getMessage());
            JsonView::error('Failed to retrieve post', 500);
        }
    }
    
    /**
     * Get a single post by URL slug
     */
    public function showBySlug(string $slug): void {
        try {
            $post = $this->postModel->findBySlugOrId($slug);
            
            if (!$post) {
                JsonView::notFound('No post found with the specified identifier');
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
            $input = Requests::only([
                'title', 'content', 'image', 'categories'
            ]);

            if (!$input) {
                JsonView::validationError(['input' => 'Invalid JSON data']);
                return;
            }

            // Validate required fields
            Requests::validate([
                'title' => 'required|string|min:2|max:255',
                'content' => 'required|string|min:5|max:10000',
                'image' => 'required|url',
                'categories' => 'string',
            ]);

            if (Requests::fails()) {
                JsonView::validationError(Requests::errors(), 'Validation failed');
                return;
            }
            
            // Production environment mimic response
            if ($_ENV['APP_ENV'] === 'production') {
                $mockPost = Mimic::generateMockPost($input);
                JsonView::success($mockPost, 'Post created successfully', 201);
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
     * Update an existing post by ID or slug (PATCH)
     */
    public function patchUpdate(string $identifier): void {
        try {
            $existingPost = $this->postModel->findBySlugOrId($identifier);
            if (!$existingPost) {
                JsonView::notFound('No post found with the specified identifier');
                return;
            }
            
            $input = Requests::only([
                'title', 'content', 'image', 'categories'
            ]);

            // Validate fields (optional for PATCH)
            Requests::validate([
                'title' => 'string|min:2|max:10',
                'content' => 'string|min:10',
                'image' => 'url',
                'categories' => 'string',
            ]);
            
            if (Requests::fails()) {
                JsonView::validationError(Requests::errors(), 'Validation failed');
                return;
            }

            // Production environment mimic response
            if ($_ENV['APP_ENV'] === 'production') {
                $mockPost = Mimic::generateMockPost(array_merge($existingPost, $input));
                JsonView::success($mockPost, 'Post updated successfully');
                return;
            }

            // Update the post using the model
            $result = $this->postModel->update($existingPost['id'], $input); 
            
            if ($result) {
                $updatedPost = $this->postModel->findBySlugOrId($existingPost['id']);
                JsonView::success($updatedPost, 'Post updated successfully');
            } else {
                JsonView::error('Failed to update post', 500);
            }
            
        } catch (Exception $e) {
            error_log("Error in PostController::patchUpdate: " . $e->getMessage());
            JsonView::error('Failed to update post', 500);
        }
    }

    /**
     * Update an existing post by ID or slug (PUT)
     */
    public function putUpdate(string $identifier): void {
        try {
            $existingPost = $this->postModel->findBySlugOrId($identifier);
            if (!$existingPost) {
                JsonView::notFound('No post found with the specified identifier');
                return;
            }
            
            $input = Requests::only([
                'title', 'content', 'image', 'categories'
            ]);

            // Validate required fields (all required for PUT)
            Requests::validate([
                'title' => 'required|string|min:2|max:10',
                'content' => 'required|string|min:10',
                'image' => 'required|url',
                'categories' => 'string',
            ]);
            
            if (Requests::fails()) {
                JsonView::validationError(Requests::errors(), 'Validation failed');
                return;
            }

            // Production environment mimic response
            if ($_ENV['APP_ENV'] === 'production') {
                $mockPost = Mimic::generateMockPost($input);
                JsonView::success($mockPost, 'Post updated successfully');
                return;
            }

            // Update the post using the model
            $result = $this->postModel->update($existingPost['id'], $input);
            
            if ($result) {
                $updatedPost = $this->postModel->findBySlugOrId($existingPost['id']);
                JsonView::success($updatedPost, 'Post updated successfully');
            } else {
                JsonView::error('Failed to update post', 500);
            }
            
        } catch (Exception $e) {
            error_log("Error in PostController::putUpdate: " . $e->getMessage());
            JsonView::error('Failed to update post', 500);
        }
    }
    
    /**
     * Delete a post by ID or slug
     */
    public function destroyBySlug(string $identifier): void {
        try {
            $existingPost = $this->postModel->findBySlugOrId($identifier);
            if (!$existingPost) {
                JsonView::notFound('No post found with the specified identifier');
                return;
            }
            
            // Production environment mimic response
            if ($_ENV['APP_ENV'] === 'production') {
                JsonView::success(null, 'Post deleted successfully');
                return;
            }
            
            // Delete the post using the model
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