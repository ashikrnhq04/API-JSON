<?php 

use src\Core\App; 
use src\Core\Database; 

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
    $data = $db->findAll("products");
} catch (Exception $e) {
    abort(500, [
        "message" => "Failed to fetch data from database",
        "error" => $e
    ], $e);
}

viewsPath("products/index.view.php", [
    "data" => $data,
]);