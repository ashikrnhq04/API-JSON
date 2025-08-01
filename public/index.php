<?php 

use Core\Router; 
use Core\Database; 
use Core\App;
use Dotenv\Dotenv;

const BASE_PATH = __DIR__ . "/../"; 

require BASE_PATH . "app/helpers/functions.php";
require BASE_PATH . "vendor/autoload.php";


// Load environment variables 
if (file_exists(BASE_PATH . '.env')) {
    $dotenv = Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
}

// Bootstrap the application
require BASE_PATH . "bootstrap/app.php";

// Get router from container
$router = App::resolve(Router::class);

require BASE_PATH . "routes/api.php";

$method = $_SERVER["_method"] ?? $_SERVER["REQUEST_METHOD"];
$uri = $_SERVER["REQUEST_URI"]; 

// Strip query string from URI for route matching
$uri = strtok($uri, '?');

$router->route($uri, $method);