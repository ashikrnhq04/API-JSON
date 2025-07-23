<?php 

return  [
    "products" => [
        "id" => "INT AUTO_INCREMENT PRIMARY KEY",
        "title" => "VARCHAR(255) NOT NULL",
        "description" => "TEXT NOT NULL",
        "price" => "DECIMAL(10,2) NOT NULL",
        "image" => "TEXT NOT NULL",
        "url" => "VARCHAR(255) NOT NULL",
        "status" => "ENUM('active','inactive') DEFAULT 'active'",
        "updated_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
        "created_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
    ],
    "categories" => [
        "id" => "INT AUTO_INCREMENT PRIMARY KEY",
        "name" => "VARCHAR(255) NOT NULL",
        "url" => "VARCHAR(255) NOT NULL",
        "created_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        "updated_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ],
    "product_category" => [
        "category_id" => "INT NOT NULL",
        "product_id" => "INT NOT NULL",
        "PRIMARY KEY (category_id, product_id)",
        "FOREIGN KEY (category_id) REFERENCES categories(id)",
        "FOREIGN KEY (product_id) REFERENCES products(id)"
    ],
    "posts" => [
        "id" => "INT AUTO_INCREMENT PRIMARY KEY",
        "title" => "VARCHAR(255) NOT NULL",
        "content" => "TEXT NOT NULL",
        "image" => "TEXT NOT NULL",
        "url" => "VARCHAR(255) NOT NULL",
        "status" => "ENUM('active','inactive') DEFAULT 'active'",
        "updated_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
        "created_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
    ],
    "post_category" => [
        "category_id" => "INT NOT NULL",
        "post_id" => "INT NOT NULL",
        "PRIMARY KEY (category_id, post_id)",   
        "FOREIGN KEY (category_id) REFERENCES categories(id)",
        "FOREIGN KEY (post_id) REFERENCES posts(id)"
    ],
    "users" => [],
]; 