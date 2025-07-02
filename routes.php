<?php


$router->get("/", "index.php");
// products routes

// GET
$router->get("/api/v1/products", "products/index.php");
$router->get("/api/v1/products/:slug", "products/single.php");

// POST
$router->post("/api/v1/products", "products/create.php");

// PATCH
$router->patch("/api/v1/products/:slug", "products/single.php");

// Product Create Form
$router->get("/api/v1/productscreate", "productcreate.php");


// user routes

// GET
$router->get("/api/v1/users", "users/index.php");
$router->get("/api/v1/users/:slug", "users/single.php");

// POST
$router->post("/api/v1/users", "users/create.php");

// PATCH
$router->patch("/api/v1/users/:slug", "users/create.php");