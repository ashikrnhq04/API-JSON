<?php 

use Core\App;
use Core\Container;
use Core\Database;
use Core\Router;

$container = new Container();

$container->bind("Core\Database", function() {
    
    $config = require BASE_PATH . "config/database.php";

    if (!isset($config["database"])) {
        throw new \RuntimeException("Database configuration not found.", 500);
    }
    
    $dbconfig = $config["database"];
    return new Database($dbconfig, $dbconfig["username"], $dbconfig["password"]);
            
});

$container->bind("Core\Router", function() {
    return new Router();
});

App::setContainer($container);