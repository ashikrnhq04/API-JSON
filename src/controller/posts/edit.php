<?php

require_once 'classes/BasePostController.php';

use src\Core\Requests;
use src\Core\Router;

class PostEditController extends BasePostController {
    
    private array $validationRules = [
        "title" => "required|string|min:2",
        "content" => "required|string|min:5", 
        "image" => "url",
        "categories" => "string",
    ];

    public function update(string $slug): void {

        try {

            
            // Validate request data
            $request = Requests::make()->validate($this->validationRules);
            
            if($request->fails()) {
                abort(400, [
                    "message" => $request->errors()
                ]);
            }
            
            $input = $request->all();
            
            // Get existing post
            $existingPost = $this->getPostBySlug($slug);
            if (empty($existingPost)) {
                abort(404, ["message" => "post not found"]);
            }
            
            // Start transaction
            if (!$this->db->beginTransaction()) {
                throw new \Exception("Failed to start database transaction");
            }
            
            // Update post data
            $this->updatePost($existingPost['id'], $input);
            
            // Update categories
            $this->updatePostCategories($existingPost['id'], $input['categories'] ?? '');

            // Commit transaction
            $this->db->commit();

            echo json_encode([
                "status" => "success",
                "message" => "post updated successfully",
                "post_id" => $existingPost['id']
            ]);

        } catch (Exception $e) {
            $this->db->rollBack();
            abort(500, [
                "message" => "Failed to update post",
                "serverError" => $e
            ]);
        }
    }

    private function updatePost(int $postId, array $input): void {
        $allowedFields = ["title", "description", "image", "price"];
        
        $updateData = array_intersect_key($input, array_flip($allowedFields));
        
        $updateData['url'] = toSlug($input['title']);

        $this->db->update("posts", $updateData, ["id" => $postId]);
    }

    private function updatePostCategories(int $postId, string $categoriesString): void {
        // Delete existing categories for the post
        $this->db->delete("post_category", ["post_id" => $postId]);

        if (empty(trim($categoriesString))) {
            return; // No categories to add
        }

        // Process new categories
        $categories = array_filter(
            array_map('trim', explode(',', $categoriesString)),
            fn($cat) => !empty($cat)
        );

        $this->handleCategoryOperations($postId, $categories);
    }
}

$routeSlug = new Router();

$slug = $routeSlug->getSlug();

$controller = new PostEditController();
$controller->update($slug); 