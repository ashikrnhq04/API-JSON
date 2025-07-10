<?php

return [
    "database" => [
        "dbname" => $_ENV["DB_NAME"] ?? "test_db",
        "host" => $_ENV["DB_HOST"] ?? "localhost",
        "port" => $_ENV["DB_PORT"] ?? 3306,
        "charset" => $_ENV["DB_CHARSET"] ?? "utf8mb4",
        "username" => $_ENV["DB_USER"] ?? "root",
        "password" => $_ENV["DB_PASS"] ?? ""
    ]
];