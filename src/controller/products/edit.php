<?php

require_once 'classes/BaseProductController.php';

use src\Core\Requests;
use src\Core\Router;

class ProductEditController extends BaseProductController {
    
    private array $validationRules = [
        "title" => "required|string|min:2",
        "description" => "required|string|min:5", 
        "price" => "required|float",
        "image" => "required|url",
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

            // Get existing product
            $existingProduct = $this->getProductBySlug($slug);
            if (empty($existingProduct)) {
                abort(404, ["message" => "Product not found"]);
            }

            // Start transaction
            if (!$this->db->beginTransaction()) {
                throw new \Exception("Failed to start database transaction");
            }

            // Update product data
            $this->updateProduct($existingProduct['id'], $input);
            
            // Update categories
            $this->updateProductCategories($existingProduct['id'], $input['categories'] ?? '');

            // Commit transaction
            $this->db->commit();

            echo json_encode([
                "status" => "success",
                "message" => "Product updated successfully",
                "product_id" => $existingProduct['id']
            ]);

        } catch (Exception $e) {
            $this->db->rollBack();
            abort(500, [
                "message" => "Failed to update product",
                "serverError" => $e
            ]);
        }
    }

    private function updateProduct(int $productId, array $input): void {
        $allowedFields = ["title", "description", "image", "price"];
        
        $updateData = array_intersect_key($input, array_flip($allowedFields));
        
        $updateData['url'] = toSlug($input['title']);

        $this->db->update("products", $updateData, ["id" => $productId]);
    }

    private function updateProductCategories(int $productId, string $categoriesString): void {
        // Delete existing categories for the product
        $this->db->delete("product_category", ["product_id" => $productId]);

        if (empty(trim($categoriesString))) {
            return; // No categories to add
        }

        // Process new categories
        $categories = array_filter(
            array_map('trim', explode(',', $categoriesString)),
            fn($cat) => !empty($cat)
        );

        $this->handleCategoryOperations($productId, $categories);
    }
}

$routeSlug = new Router();

$slug = $routeSlug->getSlug();

$controller = new ProductEditController();
$controller->update($slug);