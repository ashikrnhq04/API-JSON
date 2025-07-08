<?php

namespace src\Core;

use PDO;
use Exception;

class Database {

    private $connection;

    private $statement; 

        
    public function __construct($dbconfig, $user = 'root', $password = '') {
        
            $dsn = "mysql:" . http_build_query($dbconfig, "", ";");
            
            try {

                $this->connection = new PDO($dsn, $user, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);

            } catch (Exception $e) {

                throw new \RuntimeException("Database connection failed: " . $e->getMessage(), 500);
                
            }        
    }
    
    public function query($query) {
        
        if (empty($query)) {
            throw new \InvalidArgumentException("Query cannot be empty.", 500);
        }

        $this->statement = $this->connection->prepare($query);
        
        return $this; 
    }

    public function execute($param = []) {
        
        if (!$this->statement) {
            throw new \RuntimeException("No query has been executed.", 500);
        }
        
        if (!is_array($param)) {
            throw new \InvalidArgumentException("Parameters must be an array.", 500);
        }

        $this->statement->execute($param);

        return $this;
    }


    public function insert(string $table, array $data): self {
        if (empty($table) || empty($data)) {
            throw new \InvalidArgumentException("Table name and data cannot be empty.");
        }

        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        
        $sql = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
        
        return $this->query($sql)->execute($data);
    }

    // delete data from a table single or multiple records
    public function delete(string $table, array $conditions): int {
        if (empty($table) || empty($conditions)) {
            throw new \InvalidArgumentException("Table name and conditions cannot be empty.");
        }

        $whereClause = implode(" AND ", array_map(fn($key) => "`{$key}` = :{$key}", array_keys($conditions)));
        $sql = "DELETE FROM `{$table}` WHERE {$whereClause}";

        $this->query($sql)->execute($conditions);
        return $this->statement->rowCount();
    }
    

    public function update(string $table, array $data, array $conditions): int {
        if (empty($table) || empty($data) || empty($conditions)) {
            throw new \InvalidArgumentException("Table, data, and conditions cannot be empty.");
        }
    
        $setClause = implode(", ", array_map(fn($key) => "`{$key}` = :set_{$key}", array_keys($data)));
        $whereClause = implode(" AND ", array_map(fn($key) => "`{$key}` = :where_{$key}", array_keys($conditions)));
    
        $sql = "UPDATE `{$table}` SET {$setClause} WHERE {$whereClause}";
    
        // Prefix parameters to avoid conflicts
        $params = array_merge(
            array_combine(array_map(fn($k) => "set_{$k}", array_keys($data)), $data),
            array_combine(array_map(fn($k) => "where_{$k}", array_keys($conditions)), $conditions)
        );        
    
        $this->query($sql)->execute($params);
        return $this->statement->rowCount();
    }

    public function findAll(string $tablename, array $conditions = []) {

        paramGuard($tablename, "string", "Table name cannot be empty.");
        
        $sql = "SELECT * FROM `{$tablename}`";

        if(!empty($conditions)) {
            $whereClause = implode(" AND ", array_map(fn($key) => "`{$key}` = :{$key}", array_keys($conditions)));
            $sql .= " WHERE {$whereClause}";
        }

        // Prepare the SQL statement
        $this->query($sql);

        // Execute the statement
        $this->execute($conditions);

        // Fetch all results
        return $this->statement->fetchAll();
    }

    // find a single record with id or slug
    public function find(string $tablename, string $slug) {

        paramGuard($tablename, "string", "Table name cannot be empty.");

        paramGuard($slug, "string", "Slug cannot be empty.");

        $column = ctype_digit($slug) ? "id" : "url";

        $sql = "SELECT * FROM `{$tablename}` WHERE `{$column}` = :$column LIMIT 1";

        // Prepare the SQL statement
        $this->query($sql);

        // Execute the statement with the slug parameter
        $this->execute([$column => $slug]);

        // Fetch the single result or null
        return $this->statement->fetch() ?? null; 
        
    }

    public function select(string $table, array $columns = ["*"], array $conditions = []): array {
        $columnList = implode(", ", $columns);
        $sql = "SELECT {$columnList} FROM `{$table}`";

        if (!empty($conditions)) {
            $whereClause = implode(" AND ", array_map(fn($key) => "`{$key}` = :{$key}", array_keys($conditions)));
            $sql .= " WHERE {$whereClause}";
        }
        
        return $this->query($sql)->execute($conditions)->fetchAll();
    }

    public function fetch() {
        
        if (!$this->statement) {
            throw new \RuntimeException("No query has been executed.", 500);
        }
        
        return $this->statement->fetch();
    }

    public function fetchAll() {
        
        if (!$this->statement) {
            throw new \RuntimeException("No query has been executed.", 500);
        }
        
        return $this->statement->fetchAll();
    }   

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }


    public function createTable(string $tableName = "", array $columns): bool {

        
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
            $this->query($sql);
            $this->execute();
            return true;
        } catch (\PDOException $e) {
            error_log("Table creation failed: " . $e->getMessage());
            return false;
        }
    
    }

    public function dropTable(string $tableName): bool
    {   
        paramGuard($tableName, "string", "Table name cannot be empty.");

        $sql = "DROP TABLE IF EXISTS `{$tableName}`";

        try {
            $this->query($sql)->execute();
            return true;
        } catch (\PDOException $e) {
            error_log("Table drop failed: " . $e->getMessage());
            return false;
        }
    }

    public function hasTable(string $tableName): bool
    {

        if (empty($tableName)) {
            throw new \InvalidArgumentException("Table name cannot be empty.");
        }
    
        $sql = "SHOW TABLES LIKE :tableName";
        
        $this->query($sql)->execute(['tableName' => $tableName]);

        return !empty($this->fetchAll());

    }   
}