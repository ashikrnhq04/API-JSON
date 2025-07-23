<?php 

session_start();

use Core\Router; 
use Core\Database; 
use Core\App;
use Dotenv\Dotenv;

const BASE_PATH = __DIR__ . "/../"; 
const HTTPS_PATH = BASE_PATH . "https/";

require BASE_PATH . "https/app/helpers/functions.php";
require BASE_PATH . "vendor/autoload.php";

$_SESSION = [
    "access" => "guest"
];

// Load environment variables
$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Bootstrap the application
require HTTPS_PATH . "bootstrap/app.php";

// Get router from container
$router = App::resolve(Router::class);
require HTTPS_PATH . "routes/api.php";

$method = $_SERVER["_method"] ?? $_SERVER["REQUEST_METHOD"];
$uri = $_SERVER["REQUEST_URI"]; 

// Strip query string from URI for route matching
$uri = strtok($uri, '?');

$router->route($uri, $method);