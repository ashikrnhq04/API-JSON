<?php 


use src\Core\App;
use src\Core\Container;
use src\Core\Database; 
use src\Core\Router;


$container = new Container();

$container->bind("src\Core\Database", function() {
    
    $dbconfig = require "config.php";
    
    return new Database($dbconfig["database"], "root", "phpmyadmin");
            
});

$container->bind("src\Core\Router", function() {
    return new Router();
});

App::setContainer($container);