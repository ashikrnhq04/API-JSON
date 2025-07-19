<?php

require_once 'classes/BasePostController.php';

use src\Core\Requests;
use src\Core\Router;

class PostEditController extends BasePostController {
    
    private array $putValidationRules = [
        "title" => "required|string|min:2",
        "content" => "required|string|min:5", 
        "image" => "url",
        "categories" => "string",
    ];
    
    private array $patchValidationRules = [
        "title" => "string|min:2",
        "content" => "string|min:5", 
        "image" => "url",
        "categories" => "string",
    ];

    public function update(string $slug): void {

        $method = $_SERVER['REQUEST_METHOD'];
        
        // Choose validation rules based on HTTP method
        $validationRules = $method === 'PUT' ? $this->putValidationRules : $this->patchValidationRules;
        
        $request = Requests::make()->validate($validationRules);
        
        if ($request->fails()) {
            abort(400, [
                "message" => $request->errors(),
            ]);
        }

        if($_ENV["APP_ENV"] === "production") {
            echo json_encode([
                "status" => "success",
                "message" => "Post updated successfully",
                "method" => $method,
            ]);
            return;
        }

        try {
            $input = $request->all();
            
            // Get existing post
            $existingPost = $this->getPostBySlug($slug);
            
            if (empty($existingPost)) {
                abort(404, ["message" => "Post not found"]);
            }

            // handle production environment and mimic successful update
            if ($_ENV["APP_ENV"] === "production") {
                http_response_code(200);
                echo json_encode([
                    "status" => "success",
                    "message" => "Post updated successfully",
                    "method" => $method,
                ]);
                return;
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            // Update post data based on method
            if ($method === 'PUT') {
                $this->replacePost($existingPost['id'], $input);

                $this->replacePostCategories($existingPost['id'], $input['categories'] ?? '');

            } else {
                $this->updatePostPartial($existingPost['id'], $input);

                
                // PATCH: Only update categories if provided
                if (isset($input['categories'])) {
                    $this->replacePostCategories($existingPost['id'], $input['categories']);
                }
            }

            // Commit transaction
            $this->db->commit();

            echo json_encode([
                "status" => "success",
                "message" => "Post updated successfully",
                "method" => $method,
                "post_id" => $existingPost['id']
            ]);

        } catch (Exception $e) {
            // Safe rollback
            $this->db->rollBack();
            abort(500, [
                "message" => "Failed to update post",
                "serverError" => $e
            ]);
        }
    }

    /**
     * PUT: Replace entire post (full update)
     */
    private function replacePost(int $postId, array $input): void {
        $updateData = [
            'title' => $input['title'],
            'content' => $input['content'],
            'image' => $input['image'] ?? null,
            'url' => toSlug($input['title']),
            'id' => $postId
        ];

        $result = $this->db->query(
            "UPDATE posts SET title = :title, content = :content, image = :image, url = :url, updated_at = CURRENT_TIMESTAMP WHERE id = :id"
        )->execute($updateData);

        if (!$result) {
            throw new \Exception("Failed to update post");
        }
    }

    /**
     * PATCH: Update only provided fields (partial update)
     */
    private function updatePostPartial(int $postId, array $input): void {
        $allowedFields = ["title", "content", "image"];
        $updateData = array_intersect_key($input, array_flip($allowedFields));
        
        if (empty($updateData)) {
            return; // Nothing to update
        }

        // Add URL if title was updated
        if (isset($updateData['title'])) {
            $updateData['url'] = toSlug($updateData['title']);
        }

        // Build dynamic SQL query
        $setParts = [];
        foreach ($updateData as $field => $value) {
            $setParts[] = "`{$field}` = :{$field}";
        }
        $setParts[] = "`updated_at` = CURRENT_TIMESTAMP";

        $sql = "UPDATE posts SET " . implode(', ', $setParts) . " WHERE id = :id";
        $updateData['id'] = $postId;

        $result = $this->db->query($sql)->execute($updateData);

        if (!$result) {
            throw new \Exception("Failed to update post");
        }
    }

    /**
     * Replace post categories (used for both PUT and PATCH when categories are provided)
     */
    private function replacePostCategories(int $postId, string $categoriesString): void {
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

        if (!empty($categories)) {
            $this->handleCategoryOperations($postId, $categories);
        }
    }

}

$routeSlug = new Router();
$slug = $routeSlug->getSlug();

$controller = new PostEditController();
$controller->update($slug);