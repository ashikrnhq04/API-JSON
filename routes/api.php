<?php

use Http\Controllers\ProductController;
use Http\Controllers\PostController;

$productController = new ProductController();
$postController = new PostController();

$router->get("/", function() {
    require BASE_PATH . "app/Http/Controllers/index.php";
});

// Products routes - Reuse the same instance
$router->get("/api/v1/products", function() use ($productController) {
    $productController->index();
});

$router->get("/api/v1/products/:slug", function($slug) use ($productController) {
    $productController->showBySlug($slug);
});

$router->patch("/api/v1/products/:slug", function($slug) use ($productController) {
    $productController->patchUpdate($slug);
});

$router->put("/api/v1/products/:slug", function($slug) use ($productController) {
    $productController->putUpdate($slug);
});

$router->post("/api/v1/products", function() use ($productController) {
    $productController->create();
});

$router->delete("/api/v1/products/:slug", function($slug) use ($productController) {
    $productController->destroyBySlug($slug);
});

// Posts routes - Reuse the same instance
$router->get("/api/v1/posts", function() use ($postController) {
    $postController->index();
});

$router->get("/api/v1/posts/:slug", function($slug) use ($postController) {
    $postController->showBySlug($slug);
});

$router->post("/api/v1/posts", function() use ($postController) {
    $postController->create();
});

$router->patch("/api/v1/posts/:slug", function($slug) use ($postController) {
    $postController->patchUpdate($slug);
});

$router->put("/api/v1/posts/:slug", function($slug) use ($postController) {
    $postController->putUpdate($slug);
});

$router->delete("/api/v1/posts/:slug", function($slug) use ($postController) {
    $postController->destroyBySlug($slug);
});