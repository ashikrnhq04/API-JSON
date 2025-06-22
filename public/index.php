<?php 

use Core\Router;


const BASE_PATH = __DIR__ . "/../"; 
const PUBLIC_PATH = BASE_PATH . "public/";
const CORE_PATH = BASE_PATH . "core/";
const VIEW_PATH = BASE_PATH . "views/";


require BASE_PATH .  "functions.php";
require CORE_PATH .  "Router.php";

$router = new Router();

spl_autoload_register(function ($class) {
    dd($class);
});



require CORE_PATH .  "routes.php";

$method = $_SERVER["_method"] ?? $_SERVER["REQUEST_METHOD"];

$router->route($_SERVER["REQUEST_URI"], $method);



?>