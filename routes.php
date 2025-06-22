<?php


$router->get("/api/v1/products", "products/index.php");
$router->get("/api/v1/products/:slug", "products/single.php");