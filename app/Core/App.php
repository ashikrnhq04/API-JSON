<?php
namespace Core;
/**
 * Class App
 *
 * Provides a static interface for dependency injection container management.
 * Allows setting, getting, binding, and resolving dependencies throughout the application.
 */
class App {

    /**
     * The dependency injection container instance.
     * @var mixed
     */
    protected static $container;

    /**
     * Set the DI container instance.
     * @param mixed $container
     */
    public static function setContainer($container) {
        static::$container = $container;
    }

    /**
     * Get the DI container instance.
     * @return mixed
     */
    public static function getContainer() {
        return static::$container;
    }

    /**
     * Bind a key to a resolver in the container.
     * @param string $key
     * @param callable $resolver
     */
    public static function bind(string $key, callable $resolver) {
        static::$container->bind($key, $resolver);
    }

    /**
     * Resolve a dependency by key from the container.
     * @param string $key
     * @return mixed
     */
    public static function resolve(string $key) {
        return static::$container->resolve($key);
    }

}