<?php 

use src\Core\Router; 
use src\Core\Database; 
use src\Core\App;
use Dotenv\Dotenv;

const BASE_PATH = __DIR__ . "/../"; 

require BASE_PATH . "src/helpers/functions.php";
require BASE_PATH . "vendor/autoload.php";

// Custom autoloader for classes not using Composer
spl_autoload_register(function ($class) {
    $class = str_replace("\\", DIRECTORY_SEPARATOR, $class);
    $file = BASE_PATH . "{$class}.php";
    if (file_exists($file)) {
        require $file;
    }
});

// Load environment variables
$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Bootstrap the application
require BASE_PATH . "bootstrap.php";

// Get router from container
$router = App::resolve(Router::class);
require BASE_PATH . "routes.php";

$method = $_SERVER["_method"] ?? $_SERVER["REQUEST_METHOD"];
$uri = $_SERVER["REQUEST_URI"]; 

$router->route($uri, $method);