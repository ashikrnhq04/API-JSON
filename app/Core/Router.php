<?php
namespace Core;

use Core\Middleware\Middleware;
use Views\JsonView as JSON;
/**
 * Class Router
 *
 * Manages route definitions and dispatches requests to controllers or closures.
 * Supports HTTP verbs, access control, and route matching.
 */
class Router {
    /**
     * Array of route definitions.
     * @var array
     */
    protected $routes = [];

    /**
     * Optional slug for route matching.
     * @var string|null
     */
    protected static $slug;

    /**
     * Add a route definition.
     * @param string $method
     * @param string $uri
     * @param mixed $controller
     * @param string|null $access
     */
    public function add(string $method, string $uri, $controller, $access = null) {
        $this->routes[] = compact("method", "uri", "controller", "access");
    }

    /**
     * Add a GET route.
     * @param string $uri
     * @param mixed $controller
     * @return $this
     */
    public function get(string $uri, $controller) {
        $this->add("GET", $uri, $controller);
        return $this;
    }

    /**
     * Add a POST route.
     * @param string $uri
     * @param mixed $controller
     * @return $this
     */
    public function post(string $uri, $controller) {
        $this->add("POST", $uri, $controller);
        return $this;
    }

    /**
     * Add a PUT route.
     * @param string $uri
     * @param mixed $controller
     * @return $this
     */
    public function put(string $uri, $controller)  {
        $this->add("PUT", $uri, $controller);
        return $this;
    }

    /**
     * Add a DELETE route.
     * @param string $uri
     * @param mixed $controller
     * @return $this
     */
    public function delete(string $uri, $controller) {
        $this->add("DELETE", $uri, $controller);
        return $this;
    }

    /**
     * Add a PATCH route.
     * @param string $uri
     * @param mixed $controller
     * @return $this
     */
    public function patch(string $uri, $controller) {
        $this->add("PATCH", $uri, $controller);
        return $this;
    }

    /**
     * Restrict access to the last added route.
     * @param string $access
     * @return $this
     */
    public function only(string $access) {
        $this->routes[array_key_last($this->routes)]['access'] = $access;
        return $this;
    }

    /**
     * Match and dispatch a route based on URI and method.
     * @param string $uri
     * @param string $method
     */
    public function route(string $uri, string $method) {
        if($uri !== "/"){
            $uri = preg_replace("#/$#", "", $uri);
        }

        // Normalize URI to remove multiple slashes
        $uri = preg_replace("#/+#", "/", $uri);

        // handle static URL
        $matchedStatic = false;
        foreach ($this->routes as $route) {
            if ($route['uri'] === $uri && $route['method'] === strtoupper($method)) {
                if (isset($route['access'])) {
                    Middleware::resolve($route['access']);
                }
                // Check if controller is a closure or file path
                if (is_callable($route['controller'])) {
                    call_user_func($route['controller']);
                } else {
                    controllerPath($route['controller'], []);
                }
                $matchedStatic = true;
                break;
            }
        }
        if ($matchedStatic) {
            return;
        }
        // to handle dynamic URL
        foreach ($this->routes as $route) {


            $pattern = extractDynamicURIPattern($route['uri']);
            if (preg_match($pattern, $uri, $matches) && $route['method'] === strtoupper($method)) {

                if (isset($route['access'])) {
                    Middleware::resolve($route['access']);
                }

                array_shift($matches);
                static::$slug = $matches[0] ?? null;
                
                // Check if controller is a closure or file path
                if (is_callable($route['controller'])) {
                    // Pass the captured parameters to the closure
                    call_user_func_array($route['controller'], $matches);
                } else {
                    controllerPath($route['controller']);
                }
                
                return;
            }
            
        }
        
        $this->abort();
    }

    public static function getSlug() {
        return static::$slug;
    }

    public function abort($status = 404) {
        JSON::error("Not found", $status);        
        die();
    }

}