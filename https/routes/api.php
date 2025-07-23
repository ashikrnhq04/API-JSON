<?php

use Http\Controllers\ProductController;
use Http\Controllers\PostController;

$router->get("/", "index.php");

// products routes - Using MVC Controller Pattern
// GET
$router->get("/api/v1/products", function() {
    $controller = new ProductController();
    $controller->index();
});

$router->get("/api/v1/products/:identifier", function($identifier) {
    $controller = new ProductController();
    $controller->showBySlug($identifier);
});

// POST
$router->post("/api/v1/products", function() {
    $controller = new ProductController();
    $controller->create();
});

// PATCH
$router->patch("/api/v1/products/:identifier", function($identifier) {
    $controller = new ProductController();
    $controller->updateBySlug($identifier);
});

// PUT
$router->put("/api/v1/products/:identifier", function($identifier) {
    $controller = new ProductController();
    $controller->updateBySlug($identifier);
});

// DELETE
$router->delete("/api/v1/products/:identifier", function($identifier) {
    $controller = new ProductController();
    $controller->destroyBySlug($identifier);
});



// posts routes - Using MVC Controller Pattern
// GET
$router->get("/api/v1/posts", function() {
    $controller = new PostController();
    $controller->index();
});

$router->get("/api/v1/posts/:identifier", function($identifier) {
    $controller = new PostController();
    $controller->showBySlug($identifier);
});

// POST
$router->post("/api/v1/posts", function() {
    $controller = new PostController();
    $controller->create();
});

// PATCH
$router->patch("/api/v1/posts/:identifier", function($identifier) {
    $controller = new PostController();
    $controller->updateBySlug($identifier);
});

// PUT
$router->put("/api/v1/posts/:identifier", function($identifier) {
    $controller = new PostController();
    $controller->updateBySlug($identifier);
});

// DELETE
$router->delete("/api/v1/posts/:identifier", function($identifier) {
    $controller = new PostController();
    $controller->destroyBySlug($identifier);
});