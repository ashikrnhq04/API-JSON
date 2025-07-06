<?php

namespace src\Core;

use src\Core\Database;
use src\Core\App;

/**
 * DBTools class for managing database schemas.
 * Provides methods to create tables and check for existing tables.
 */

class DBTools
{
    protected Database $db;

    public function __construct()
    {

        try {
            $this->db = App::resolve(Database::class);
        } catch(\Exception $e) {
            abort(500, [
                "message" => "Database connection failed", 
                "error" => $e
            ]);
        }
    }

    public function hasTable(string $tableName): bool
    {
        if (empty($this->db)) {
            throw new \RuntimeException("Database connection is not established.");
        }
                
        if (empty($tableName)) {
            throw new \InvalidArgumentException("Table name cannot be empty.");
        }
    
        $sql = "SHOW TABLES LIKE :tableName";
        
        $this->db->query($sql);

        $this->db->execute(['tableName' => $tableName]);

        return !empty($this->db->fetchAll());

    }

    public function createTable(string $tableName = "", array $columns): bool {

        dbCheck($this->db, "Database connection is not established.");
        
        paramGuard($tableName, "string",  "Table name cannot be empty.");
        paramGuard($columns, "array", "Columns definition must be an array.");

        $columnsSql = [];

        foreach ($columns as $name => $definition) {
            
            // To manage constraints and parimary keys
            if (is_int($name)) {
                $columnsSql[] = $definition; 
            } else {
                $columnsSql[] = "`{$name}` {$definition}"; 
            }
        }

        $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (\n    " . implode(",\n    ", $columnsSql) . "\n);";
    
    
        try {
            $this->db->query($sql);
            $this->db->execute(); // Execute the query
            return true;
        } catch (\PDOException $e) {
            error_log("Table creation failed: " . $e->getMessage());
            return false;
        }
    
    }

    public function dropTable(string $tableName): bool
    {
        dbCheck($this->db, "Database connection is not established.");
        
        paramGuard($tableName, "string", "Table name cannot be empty.");

        $sql = "DROP TABLE IF EXISTS `{$tableName}`";

        try {
            $this->db->query($sql);
            $this->db->execute(); // Execute the query
            return true;
        } catch (\PDOException $e) {
            error_log("Table drop failed: " . $e->getMessage());
            return false;
        }
    }

}