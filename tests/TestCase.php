<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Core\App;
use Core\Container;
use Core\Database;
use Core\Router;
use Dotenv\Dotenv;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Define constants if not already defined
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', __DIR__ . '/../');
        }
        
        // Load helper functions
        require_once BASE_PATH . "app/helpers/functions.php";
        
        // Load environment variables (optional)
        if (file_exists(BASE_PATH . '.env')) {
            $dotenv = Dotenv::createImmutable(BASE_PATH);
            $dotenv->load();
        }
        
        // Setup application container
        $this->setupApplication();
    }
    
    protected function setupApplication(): void
    {
        $container = new Container();
        
        $container->bind("Core\Database", function() {
            $dbconfig = require BASE_PATH . "config/database.php";
            if (!isset($dbconfig["database"])) {
                throw new \RuntimeException("Database configuration not found.", 500);
            }
            
            $config = $dbconfig["database"];
            return new Database($config, $config["username"], $config["password"]);
        });
        
        $container->bind("Core\Router", function() {
            return new Router();
        });
        
        App::setContainer($container);
    }
    
    protected function makeRequest(string $method, string $uri, array $data = []): array
    {
        // Backup original server variables
        $originalServer = $_SERVER;
        $originalPost = $_POST;
        $originalGet = $_GET;
        
        // Set up request environment
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        
        if ($method === 'POST' || $method === 'PATCH') {
            $_POST = $data;
        } elseif ($method === 'GET') {
            $_GET = $data;
        }
        
        // Capture output
        ob_start();
        
        try {
            // Get router and load routes
            $router = App::resolve(Router::class);
            require BASE_PATH . "routes/api.php";
            
            // Route the request
            $router->route($uri, $method);
            
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
            
            // Restore original server variables
            $_SERVER = $originalServer;
            $_POST = $originalPost;
            $_GET = $originalGet;
        }
        
        return json_decode($output, true) ?: [];
    }
}
