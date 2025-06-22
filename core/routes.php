<?php


$router->get("/api/v1/products", "products/index.php");
$router->get("/api/v1/products/:uri", "products/single.php");