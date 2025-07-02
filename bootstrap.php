<?php 


use src\Core\App;
use src\Core\Container;
use src\Core\Database; 


$container = new Container();


$container->bind("database", function() {

        $dbconfig = require "config.php";
        return new Database($dbconfig["database"], "root", "phpmyadmin");     
            
}); 

App::setContainer($container);