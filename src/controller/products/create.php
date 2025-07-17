<?php

use src\Core\Requests; 
use src\Core\App; 
use src\Core\Database; 
use src\Core\SchemaManager;

require_once "classes/BaseProductController.php";

class ProductSaveController extends BaseProductController {

    protected $validationRules = [
        "title" => "required|string|min:2",
        "description" => "required|string|min:5",
        "price" => "required|float",
        "image" => "required|url",
        "categories" => "string",
    ];

    public function save(): void {
        $request = Requests::make()->validate($this->validationRules);
        
        if ($request->fails()) {
            abort(400, [
                "message" => $request->errors()
            ]);
        }


        $input = $request->all();

        if($_ENV["APP_ENV"] === "production") {

            echo json_encode([
                "status" => "success",
                "message" => "Product saved successfully",
            ]);
            return;
                
        }

        try {
            // extract categories to insert to the DB seperately
            $categories = explode(",", $input["categories"] ?? "");
          

            // catch and insert only the right data to product table
            $productData = ["title", "description", "image", "price", "url"];
            
            
            // Start transaction
            if (!$this->db->beginTransaction()) {
                throw new \Exception("Failed to start database transaction");
            }

            $this->handleTableOperations();

            $this->db->insert("products", array_intersect_key([...$input, 'url' => toSlug($input['title'])], array_flip($productData)));

            // get the last inserted product id
            $productId = $this->db->lastInsertId();
            
            if (empty($productId)) {
                throw new \Exception("Failed to get product ID");
            }

           
            // Commit transaction
            $this->db->commit();

            echo json_encode([
                "status" => "success",
                "message" => "Product saved successfully",
            ]);
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            abort(500, [
                "message" => "Failed to save product",
                "serverError" => $e
            ]);
        }
    }

}

$productSave = new ProductSaveController();
$productSave->save();