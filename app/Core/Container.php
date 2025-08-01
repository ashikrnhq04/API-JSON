<?php
namespace Core;
/**
 * Class Container
 *
 * Simple dependency injection container. Manages bindings and resolves dependencies.
 */
class Container {

    /**
     * Array of key => resolver bindings.
     * @var array
     */
    protected $bindings = [];

    /**
     * Register a resolver for a key.
     * @param string $key
     * @param callable $resolver
     */
    public function bind(string $key, callable $resolver) {
        $this->bindings[$key] = $resolver;
    }

    /**
     * Resolve and return the value for a key.
     * Throws exception if not found.
     * @param string $key
     * @return mixed
     * @throws \Exception
     */
    public function resolve(string $key){
        if(!array_key_exists($key, $this->bindings)) {
            throw new \Exception("No binding found for the {$key}");
        }
        return call_user_func($this->bindings[$key]);
    }
}