<?php 


use src\Core\App; 
use src\Core\Database; 
use src\Core\DBTools; 
use src\Core\SchemaManager;

$dbtools = new DBTools();

$error = [];
$data = [];


// Resolve the database instance from the App container
try {
    $db = App::resolve(Database::class);
} catch(\Exception $e) {
    abort(500, [
        "message" => "Database connection failed", 
        "error" => $e
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
    

    $db->insert("products", [...$input, 'url' => toSlug($input['title'])]);
    
    echo json_encode([
        "status" =>  "success",
        "message" => "Product created successfully"
    ]);
    exit;
    
} catch (Exception $e) {
    abort(500, [
        "message" => "Failed to save data to database",
        "error" => $e
    ]);
}