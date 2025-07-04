<?php 


use src\Core\Router; 
use src\Core\Database; 

const BASE_PATH = __DIR__ . "/../"; 

require BASE_PATH .  "src/helpers/functions.php";

spl_autoload_register(function ($class) {
    $class = str_replace("\\", DIRECTORY_SEPARATOR, $class);
    require BASE_PATH . "{$class}.php";
});

require BASE_PATH .  "bootstrap.php";


$router = new Router();
require BASE_PATH .  "routes.php";


$method = $_SERVER["_method"] ?? $_SERVER["REQUEST_METHOD"];
$uri = $_SERVER["REQUEST_URI"]; 


$router->route($uri, $method);

?>