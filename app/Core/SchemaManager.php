<?php
namespace Core;
/**
 * Class SchemaManager
 *
 * Loads and provides access to database schema definitions.
 */
class SchemaManager
{
    /**
     * Stores loaded schema definitions.
     * @var array
     */
    protected static array $schema = [];

    /**
     * Load schema definitions from file.
     */
    public static function load(): void
    {
        self::$schema = require BASE_PATH . 'src/schema/DBSchema.php';
    }

    /**
     * Get schema for a specific key.
     * @param string $key
     * @return array
     */
    public static function get(string $key): array
    {
        if (empty(self::$schema)) {
            self::load(); // Lazy-load the schema
        }
        return self::$schema[$key] ?? [];
    }

    /**
     * Get all schema definitions.
     * @return array
     */
    public static function all(): array
    {
        if (empty(self::$schema)) {
            self::load();
        }
        return self::$schema;
    }
}