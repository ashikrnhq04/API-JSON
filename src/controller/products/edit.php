<?php

require_once 'classes/BaseProductController.php';

use src\Core\Requests;
use src\Core\Router;

class ProductEditController extends BaseProductController {
    
    private array $putValidationRules = [
        "title" => "required|string|min:2",
        "description" => "required|string|min:5", 
        "price" => "required|float",
        "image" => "required|url",
        "categories" => "string",
    ];
    private array $patchValidationRules = [
        "title" => "string|min:2",
        "description" => "string|min:5", 
        "price" => "float",
        "image" => "url",
        "categories" => "string",
    ];

    public function update(string $slug): void {
        $method = $_SERVER['REQUEST_METHOD'];

        $validationRules = $method === 'PUT' ? $this->putValidationRules : $this->patchValidationRules;
        
        $request = Requests::make()->validate($validationRules);

        if ($request->fails()) {
            abort(400, [
                "message" => $request->errors()
            ]);
        }
        
        if($_ENV["APP_ENV"] === "production") {
            echo json_encode([
                "status" => "success",
                "message" => "Product updated successfully",
                "method" => $method,
            ]);
            return;
        }
        
        try {
            $input = $request->all();

            $existingProduct = $this->getProductBySlug($slug);

            if (empty($existingProduct)) {
                abort(404, [
                    "message" => "Product not found"
                ]);
            }

            $this->db->beginTransaction();

            if ($method === 'PUT') {
                
                $this->replaceProduct($existingProduct['id'], $input);
                
                // Handle categories: always replace categories (even if empty)
                $this->replaceProductCategories($existingProduct['id'], $input['categories'] ?? '');

            } else {

                $this->updateProductPartial($existingProduct['id'], $input);
                
                // Handle categories: only update categories if provided
                if (isset($input['categories'])) {
                    $this->replaceProductCategories($existingProduct['id'], $input['categories']);
                }
            }
            $this->db->commit();
            
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Product updated successfully",
                "method" => $method,
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

    private function replaceProduct(int $productId, array $input): void {
        $updateData = [
            'title' => $input['title'],
            'description' => $input['description'],
            'image' => $input['image'],
            'price' => $input['price'],
            'url' => toSlug($input['title']),
            'id' => $productId
        ];
        $this->db->query(
            "UPDATE products SET title = :title, description = :description, image = :image, price = :price, url = :url WHERE id = :id"
        )->execute($updateData);
    }

    private function updateProductPartial(int $productId, array $input): void {
        $allowedFields = ["title", "description", "image", "price"];
        $updateData = array_intersect_key($input, array_flip($allowedFields));
        if (isset($updateData['title'])) {
            $updateData['url'] = toSlug($updateData['title']);
        }
        if (empty($updateData)) {
            return;
        }
        $setParts = [];
        foreach ($updateData as $field => $value) {
            $setParts[] = "$field = :$field";
        }
        $sql = "UPDATE products SET " . implode(', ', $setParts) . " WHERE id = :id";
        $updateData['id'] = $productId;
        $this->db->query($sql)->execute($updateData);
    }

    private function replaceProductCategories(int $productId, string $categoriesString): void {
        $this->db->delete("product_category", ["product_id" => $productId]);
        if (empty(trim($categoriesString))) {
            return;
        }
        $categories = array_filter(
            array_map('trim', explode(',', $categoriesString)),
            fn($cat) => !empty($cat)
        );
        if (!empty($categories)) {
            $this->handleCategoryOperations($productId, $categories);
        }
    }
}

$routeSlug = new Router();

$slug = $routeSlug->getSlug();

$controller = new ProductEditController();
$controller->update($slug);