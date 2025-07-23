<?php 

use Core\App;
use Core\Container;
use Core\Database;
use Core\Router;

$container = new Container();

$container->bind("Core\Database", function() {
    
    $dbconfig = require HTTPS_PATH . "config/database.php";
    if (!isset($dbconfig["database"])) {
        throw new \RuntimeException("Database configuration not found.", 500);
    }
    
    $config = $dbconfig["database"];
    return new Database($config, $config["username"], $config["password"]);
            
});

$container->bind("Core\Router", function() {
    return new Router();
});

App::setContainer($container);