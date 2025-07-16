<?php

// Test environment variable loading

require_once 'vendor/autoload.php';

use Dotenv\Dotenv;

echo "🔍 Testing environment variable loading...\n\n";

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "📄 .env file contents:\n";
if (file_exists('.env')) {
    echo file_get_contents('.env') . "\n";
} else {
    echo "❌ .env file not found!\n";
}

echo "\n🔧 Environment variables after Dotenv load:\n";
echo "DB_NAME (getenv): " . (getenv("DB_NAME") ?: "NOT SET") . "\n";
echo "DB_HOST (getenv): " . (getenv("DB_HOST") ?: "NOT SET") . "\n";
echo "DB_USER (getenv): " . (getenv("DB_USER") ?: "NOT SET") . "\n";
echo "DB_PASS (getenv): " . (getenv("DB_PASS") ?: "NOT SET") . "\n";

echo "\n🔧 Environment variables using \$_ENV:\n";
echo "DB_NAME (\$_ENV): " . ($_ENV["DB_NAME"] ?? "NOT SET") . "\n";
echo "DB_HOST (\$_ENV): " . ($_ENV["DB_HOST"] ?? "NOT SET") . "\n";
echo "DB_USER (\$_ENV): " . ($_ENV["DB_USER"] ?? "NOT SET") . "\n";
echo "DB_PASS (\$_ENV): " . ($_ENV["DB_PASS"] ?? "NOT SET") . "\n";

echo "\n🔧 Environment variables using \$_SERVER:\n";
echo "DB_NAME (\$_SERVER): " . ($_SERVER["DB_NAME"] ?? "NOT SET") . "\n";
echo "DB_HOST (\$_SERVER): " . ($_SERVER["DB_HOST"] ?? "NOT SET") . "\n";

echo "\n📊 Config file test:\n";
$config = require 'config.php';
echo "Config DB_NAME: " . $config["database"]["dbname"] . "\n";
echo "Config DB_HOST: " . $config["database"]["host"] . "\n";
echo "Config DB_USER: " . $config["database"]["username"] . "\n";
echo "Config DB_PASS: " . $config["database"]["password"] . "\n";

echo "\n✅ Test complete!\n";