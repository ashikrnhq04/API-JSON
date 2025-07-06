<?php 


use src\Core\App; 
use src\Core\Database; 
use src\Core\DBTools; 
use src\Core\SchemaManager;

$dbtools = new DBTools();



// Resolve the database instance from the App container
try {
    $db = App::resolve(Database::class);
} catch(\Exception $e) {
    abort(500, [
        "message" => "Database connection failed", 
        "serverError" => $e
    ]);
}

try {

    // Ensure products table exists
    if (!$dbtools->hasTable("products")) {
        $dbtools->createTable("products", SchemaManager::get("products"));
    }

    // Ensure categories table exists
    if (!$dbtools->hasTable("categories")) {
        $dbtools->createTable("categories", SchemaManager::get("categories"));
    }

    // Ensure product_category table exists
    if (!$dbtools->hasTable("product_category")) {
        $dbtools->createTable("product_category", SchemaManager::get("product_category"));
    }
    
    // input from the Request class
    $input = $request->all();


    // extract categories to insert to the DB seperately
    $categories = explode(",", $input["category"] ?? "");


    // catch and insert only the right data to product table
    $productData = ["title", "description", "image", "price", "url"];

    $db->insert("products", array_intersect_key($input, array_flip($productData)));

    // get the last inserted product id
    $lastInsertedProductID = $db->lastInsertId();

    if(!empty($categories)) {
        
        foreach($categories as $category) {
            $category = trim($category);

            // skip empty categories
            if (empty($category)) {
                continue;
            }

            // check if category already exists
            $existingCategory = $db->select("categories", ["id"], ["name" => $category]);

            if ($existingCategory) {
                // insert the product-category relationship
                $db->insert("product_category", [
                    "product_id" => $lastInsertedProductID,
                    "category_id" => $existingCategory[0]["id"]
                ]);
                continue;
            }

            // insert categories to categories table
            $db->insert("categories", ["name" => $category, 'url' => toSlug($category)]);

            // get the last inserted category id
            $categoryId = $db->lastInsertId();

            // insert the product-category relationship
            $db->insert("product_category", [
                "product_id" => $lastInsertedProductID,
                "category_id" => $categoryId
            ]);
        }    
    }
    echo json_encode([
        "status" =>  "success",
        "message" => "Product created successfully"
    ]);
    exit;
} catch (Exception $e) {
    abort(500, [
        "message" => "Failed to save data to database",
        "serverError" => $e
    ]);
}