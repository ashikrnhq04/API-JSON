<?php

namespace Core; 

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

    public function route(string $uri, string $method) {

        
        foreach($this->routes as $route){
            if($route["uri"] === $uri && $route["method"] === strtoupper($method)) {
                return require VIEW_PATH . $route['controller'];
            }
        };
        
        $this->abort();

    }

    public function abort($status = 404) {
        require VIEW_PATH . "{$status}.php";
        die();
    }

}