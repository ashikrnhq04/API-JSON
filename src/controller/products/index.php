<?php 

use src\Core\App; 
use src\Core\Database; 

$error = [];
$data = [];


try {
    $db = App::resolve(Database::class);
} catch(\Exception $e) {
    abort(500, [
        "message" => "Database connection failed",
        "error" => $e
    ]);
}

/**
 * Fetches products and their associated categories from the database.
 *
 * @param Database $db The database connection instance.
 * @return array An array of products with their categories.
 */
function getProducts(Database $db) {
    $sql = "SELECT p.*, GROUP_CONCAT(c.name SEPARATOR ', ') as categories FROM products p
    LEFT JOIN product_category pc ON p.id = pc.product_id
    LEFT JOIN categories c ON c.id = pc.category_id GROUP BY p.id
    "; 
    
    $results = $db->query($sql)->execute()->fetchAll();
    
    // Convert categories string to array
    foreach ($results as &$product) {
        $product['categories'] = !empty($product['categories']) 
            ? explode(', ', $product['categories']) 
            : [];
    }
    
    return $results;
}



try {
    $data = getProducts($db);
} catch (Exception $e) {
    abort(500, [
        "message" => "Failed to fetch data from database",
        "error" => $e
    ], $e);
}

viewsPath("products/index.view.php", [
    "data" => $data,
]);