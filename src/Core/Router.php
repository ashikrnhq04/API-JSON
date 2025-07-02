<?php

namespace src\Core;

class Router {
    protected $routes = []; 

    public function add(string $method, string $uri, string $controller) {

        $this->routes[] = compact("method", "uri", "controller");

    }

    public function get(string $uri, string $controller) {

        $this->add("GET", $uri, $controller);

    }

    public function post(string $uri, string $controller) {

        $this->add("POST", $uri, $controller);

    }

    public function put(string $uri, string $controller)  {

        $this->add("PUT", $uri, $controller);    

    }

    public function delete(string $uri, string $controller) {

        $this->add("DELETE", $uri, $controller);
    }

    public function patch(string $uri, string $controller) {

        $this->add("PATCH", $uri, $controller);
    }

    public function route(string $uri, string $method) {

        if($uri !== "/"){
            $uri = preg_replace("#/$#", "", $uri);
        }


        

        // handle static URL 
        foreach ($this->routes as $route) {
            if ($route['uri'] === $uri && $route['method'] === strtoupper($method)) {
                controllerPath($route['controller'], []);
                return;
            }
        }
        
        // to handle dynamic URL
        foreach ($this->routes as $route) {
            $pattern = extractDynamicURIPattern($route['uri']);
        
            if (preg_match($pattern, $uri, $matches) && $route['method'] === strtoupper($method)) {
                array_shift($matches);
                $slug = $matches[0] ?? null;
                controllerPath($route['controller'], ["slug" => $slug]);
                return;
            }
        }
        
        $this->abort();
    }

    public function abort($status = 404) {
        http_response_code($status);
        header('Content-type: application/json');
        header('Access-Control-Allow-Origin: *');

        echo json_encode((object)[], JSON_PRETTY_PRINT);
        
        die();
    }

}