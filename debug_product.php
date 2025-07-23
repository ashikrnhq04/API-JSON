<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

const BASE_PATH = __DIR__ . "/";

require_once 'vendor/autoload.php';
require_once 'src/helpers/functions.php';

use Dotenv\Dotenv;
use src\Core\App;
use src\Core\Container;
use src\Core\Database;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize the container like in bootstrap
$container = new Container();

$container->bind("src\Core\Database", function() {
    $dbconfig = require "config.php";
    if (!isset($dbconfig["database"])) {
        throw new \RuntimeException("Database configuration not found.", 500);
    }
    
    $config = $dbconfig["database"];
    return new Database($config, $config["username"], $config["password"]);
});

App::setContainer($container);

// Test the ProductController directly
require_once 'src/models/Product.php';
require_once 'src/views/JsonView.php';
require_once 'src/controller/ProductController.php';

try {
    echo "Testing Product creation...\n";
    
    $data = [
        'title' => 'Test Product',
        'description' => 'A test product description that is long enough',
        'price' => 99.99,
        'image' => 'https://example.com/image.jpg',
        'category_ids' => [1]
    ];
    
    $product = new Product();
    $result = $product->create($data);
    
    echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
