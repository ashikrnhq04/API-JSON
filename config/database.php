<?php

// Load environment variables if .env file exists
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        
        $pos = strpos($line, '=');
        if ($pos !== false) {
            $name = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            
            // Remove quotes if present
            if (strlen($value) >= 2) {
                if (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
            }
            
            $_ENV[$name] = $value;
        }
    }
}

return [
    "database" => [
        "dbname" => $_ENV["DB_NAME"] ?? "commercio_db",
        "host" => $_ENV["DB_HOST"] ?? "localhost",
        "port" => (int)($_ENV["DB_PORT"] ?? 3306),
        "charset" => $_ENV["DB_CHARSET"] ?? "utf8mb4",
        "username" => $_ENV["DB_USERNAME"] ?? "root",
        "password" => $_ENV["DB_PASSWORD"] ?? ""
    ]
];
