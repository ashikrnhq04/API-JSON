<?php 

namespace Core; 

class App {

    protected static $container; 

    public static function setContainer($container) {
        static::$container = $container; 
    }

    public static function getContainer() {
        return static::$container; 
    }

    public static function bind(string $key, callable $resolver) {
        static::$container->bind($key, $resolver); 
    }

    public static function resolve(string $key) {
        return static::$container->resolve($key); 
    }

}