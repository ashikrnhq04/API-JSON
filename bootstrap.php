<?php 

use src\Core\App;
use src\Core\Container;
use src\Core\Database;
use src\Core\Router;

$container = new Container();

$container->bind("src\Core\Database", function() {
    
    $dbconfig = require "config.php";
    if (!isset($dbconfig["database"])) {
        throw new \RuntimeException("Database configuration not found.", 500);
    }
    
    $config = $dbconfig["database"];
    return new Database($config, $config["username"], $config["password"]);
            
});

$container->bind("src\Core\Router", function() {
    return new Router();
});

App::setContainer($container);