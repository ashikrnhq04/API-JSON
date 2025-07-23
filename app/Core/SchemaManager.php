<?php

namespace Core;
class SchemaManager
{
    protected static array $schema = [];

    public static function load(): void
    {
        self::$schema = require BASE_PATH . 'src/schema/DBSchema.php';
    }

    public static function get(string $key): array
    {
        if (empty(self::$schema)) {
            self::load(); // Lazy-load the schema
        }

        return self::$schema[$key] ?? [];
    }

    public static function all(): array
    {
        if (empty(self::$schema)) {
            self::load();
        }

        return self::$schema;
    }
}