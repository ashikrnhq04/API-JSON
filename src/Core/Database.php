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


    public function insert(string $tableName, array $data) {
        

        paramGuard($tableName, "string", "Table name cannot be empty.");
        
        paramGuard($data, "array", "Data must be an array.");
        

        $columns = implode(", ", array_keys($data));
        
        // Ensure that the data array is not empty
        if (empty($data)) {
            throw new \InvalidArgumentException("Data array cannot be empty.", 500);
        }

        $placeholders = implode(", ", array_fill(0, count($data), '?'));
        
        $query = "INSERT INTO `$tableName` ($columns) VALUES ($placeholders)";
        
        $this->query($query);
        
        return $this->execute(array_values($data));

    }

    public function findAll(string $tablename, array $conditions = []) {

        paramGuard($tablename, "string", "Table name cannot be empty.");
        
        $sql = "SELECT * FROM `{$tablename}`";

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

        $column = ctype_digit($slug) ? "id" : "slug";

        $sql = "SELECT * FROM `{$tablename}` WHERE `{$column}` = :$column LIMIT 1";

        // Prepare the SQL statement
        $this->query($sql);

        // Execute the statement with the slug parameter
        $this->execute([$column => $slug]);

        // Fetch the single result or null
        return $this->statement->fetch() ?? null; 
        
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

    public function select(string $tableName, array $columns = ["*"], array $conditions = []) {
        
        paramGuard($tableName, "string", "Table name cannot be empty.");
        

        $columnList = implode(", ", $columns);
        
        $sql = "SELECT {$columnList} FROM `{$tableName}`";

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", array_map(function($key) {
                return "`{$key}` = :{$key}";
            }, array_keys($conditions)));
        }
        
        $this->query($sql);
        
        return $this->execute($conditions)->fetchAll();
        
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}