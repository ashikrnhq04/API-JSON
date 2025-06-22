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

        $uri = preg_replace("#/$#", "", $uri);

        foreach ($this->routes as $route) {

            // replace dynamic slug with actual url part
            $pattern = preg_replace('#:([\w]+)#', '([^/]+)', $route['uri']);
            $pattern = "#^" . $pattern . "$#";

            

            if (preg_match($pattern, $uri, $matches) && $route['method'] === strtoupper($method)) {

                // remove full match
                array_shift($matches);
                
                // extract the slag
                $slug  = $matches;
                
                return require VIEW_PATH . $route['controller'];
            }
        }
    
        $this->abort();
    }

    public function abort($status = 404) {
        require VIEW_PATH . "{$status}.php";
        die();
    }

}