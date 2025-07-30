<?php

use Http\Controllers\ProductController;
use Http\Controllers\PostController;

$productController = new ProductController();
$postController = new PostController();

$router->get("/", function() {
    require BASE_PATH . "app/Http/Controllers/index.php";
});

// Products routes - With API rate limiting
$router->get("/api/v1/products", function() use ($productController) {
    $productController->index();
})->only('rate_limit_api');

$router->get("/api/v1/products/:slug", function($slug) use ($productController) {
    $productController->showBySlug($slug);
})->only('rate_limit_api');

$router->patch("/api/v1/products/:slug", function($slug) use ($productController) {
    $productController->patchUpdate($slug);
})->only('rate_limit_api');

$router->put("/api/v1/products/:slug", function($slug) use ($productController) {
    $productController->putUpdate($slug);
})->only('rate_limit_api');

$router->post("/api/v1/products", function() use ($productController) {
    $productController->create();
})->only('rate_limit_api');

$router->delete("/api/v1/products/:slug", function($slug) use ($productController) {
    $productController->destroyBySlug($slug);
})->only('rate_limit_api');

// Posts routes - With API rate limiting
$router->get("/api/v1/posts", function() use ($postController) {
    $postController->index();
})->only('rate_limit_api');

$router->get("/api/v1/posts/:slug", function($slug) use ($postController) {
    $postController->showBySlug($slug);
})->only('rate_limit_api');

$router->post("/api/v1/posts", function() use ($postController) {
    $postController->create();
})->only('rate_limit_api');

$router->patch("/api/v1/posts/:slug", function($slug) use ($postController) {
    $postController->patchUpdate($slug);
})->only('rate_limit_api');

$router->put("/api/v1/posts/:slug", function($slug) use ($postController) {
    $postController->putUpdate($slug);
})->only('rate_limit_api');

$router->delete("/api/v1/posts/:slug", function($slug) use ($postController) {
    $postController->destroyBySlug($slug);
})->only('rate_limit_api');

// Rate limit status endpoint
$router->get("/api/v1/rate-limit-status", function() {
    $rateLimit = new \Core\RateLimit();
    $info = $rateLimit->getInfo('api');
    \Views\JsonView::success($info, 'Rate limit status retrieved successfully');
})->only('rate_limit_default');