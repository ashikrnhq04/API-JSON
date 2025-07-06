<?php

use src\Core\App; 
use src\Core\Database; 

$error = [];
$data = [];


function getProduct($db, $slug) {
    $column = ctype_digit($slug) ? "id" : "url";

    $sql = "SELECT p.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS categories 
    FROM products p
    LEFT JOIN product_category pc ON p.id = pc.product_id
    LEFT JOIN categories c ON c.id = pc.category_id 
    WHERE p.`{$column}` = :{$column}
    GROUP BY p.id LIMIT 1";

    $result = $db->query($sql)->execute([$column => $slug])->fetch();
    
    return $result ? array_merge($result, [
        'categories' => isset($result['categories']) && $result['categories'] !== null
            ? array_map('trim', explode(',', $result['categories']))
            : []
    ]) : [];
}


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
    $data = getProduct($db, $slug);
} catch (Exception $e) {
   abort(500, [
    "message" => "Failed to fetch data from Database",
    "serverError" => $e
]);
}


viewsPath("products/single.view.php", [
    "data" => $data,
]);