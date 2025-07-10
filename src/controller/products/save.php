<?php

require_once 'classes/BaseProductController.php';

use src\Core\Requests;

class ProductSaveController extends BaseProductController {
    
    private array $validationRules = [
        "title" => "required|string|min:2|max:255",
        "description" => "required|string|min:10|max:1000", 
        "price" => "required|float|min:0.01",
        "image" => "required|url|max:500",
        "categories" => "string|max:500",
    ];

    public function store(): void {
        try {
            // Validate request data using powerful Requests validator
            $request = Requests::make()->validate($this->validationRules);
            $input = $request->all();

            // Start transaction
            if (!$this->db->beginTransaction()) {
                throw new \Exception("Failed to start database transaction");
            }

            // Create the product
            $productId = $this->createProduct($input);
            
            // Handle categories
            $this->handleProductCategories($productId, $input['categories'] ?? '');

            // Commit transaction
            $this->db->commit();

            echo json_encode([
                "status" => "success",
                "message" => "Product created successfully",
                "product_id" => $productId
            ]);

        } catch (Exception $e) {
            // Only rollback if transaction is active
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            abort(500, [
                "message" => "Failed to save product",
                "serverError" => $e
            ]);
        }
    }

    private function createProduct(array $input): int {
        $allowedFields = ["title", "description", "image", "price"];
        
        $productData = array_intersect_key($input, array_flip($allowedFields));
        $productData['url'] = toSlug($input['title']);

        $this->db->insert("products", $productData);
        
        return (int) $this->db->lastInsertId();
    }

    private function handleProductCategories(int $productId, string $categoriesString): void {
        if (empty(trim($categoriesString))) {
            return; // No categories to add
        }

        // Process categories
        $categories = array_filter(
            array_map('trim', explode(',', $categoriesString)),
            fn($cat) => !empty($cat)
        );

        $this->handleCategoryOperations($productId, $categories);
    }
}

$controller = new ProductSaveController();
$controller->store();
