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
    $data = $db->find("products", $slug);;
} catch (Exception $e) {
   abort(500, [
    "message" => "Failed to fetch data from Database",
]);
}


viewsPath("products/single.view.php", [
    "data" => $data,
]);