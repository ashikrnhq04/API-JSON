<?php


$router->get("/", "index.php");

// products routes
// GET
$router->get("/api/v1/products", "products/index.php");
$router->get("/api/v1/products/:slug", "products/single.php");

// POST
$router->post("/api/v1/products", "products/create.php");

// PATCH
$router->patch("/api/v1/products/:slug", "products/edit.php");

// PUT
$router->put("/api/v1/products/:slug", "products/edit.php");


// DELETE
$router->delete("/api/v1/products/:slug", "products/destroy.php");



// user routes
// GET
$router->get("/api/v1/users", "users/index.php");
$router->get("/api/v1/users/:slug", "users/single.php");

// POST
$router->post("/api/v1/users", "users/create.php");

// PATCH
$router->patch("/api/v1/users/:slug", "users/create.php");


// posts routes
// GET
$router->get("/api/v1/posts", "posts/index.php");
$router->get("/api/v1/posts/:slug", "posts/single.php");

// POST
$router->post("/api/v1/posts", "posts/create.php");

// PATCH
$router->patch("/api/v1/posts/:slug", "posts/edit.php");
$router->put("/api/v1/posts/:slug", "posts/edit.php");