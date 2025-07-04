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
        
        if (empty($tableName) || empty($data)) {
            throw new \InvalidArgumentException("Table name and data cannot be empty.", 500);
        }

        $columns = implode(", ", array_keys($data));
        
        if (empty($columns)) {
            throw new \InvalidArgumentException("Data must contain at least one column.", 500);
        }

        $placeholders = implode(", ", array_fill(0, count($data), '?'));
        
        $query = "INSERT INTO `$tableName` ($columns) VALUES ($placeholders)";
        
        $this->query($query);
        
        return $this->execute(array_values($data));

    }

    public function findAll(string $tablename, array $conditions = []) {

        if (empty($tablename)) {
            throw new \InvalidArgumentException("Table name cannot be empty.", 500);
        }
        
        if (!$this->connection) {
            throw new \RuntimeException("Database connection is not established.", 500);
        }

        $sql = "SELECT * FROM `{$tablename}`";

        // Prepare the SQL statement
        $this->query($sql);

        // Execute the statement
        $this->execute($conditions);

        // Fetch all results
        return $this->statement->fetchAll();
    }

    public function find(string $tableName, string|int $value) {
        
        if (empty($tableName) || empty($value)) {
            throw new \InvalidArgumentException("Table name and value are required.", 500);
        }
    
        if (!$this->connection) {
            throw new \RuntimeException("Database connection is not established.", 500);
        }
        
        $column = ctype_digit((string)$value) ? 'id' : 'url';
    
        $sql = "SELECT * FROM `{$tableName}` WHERE `{$column}` = :value";
    
        // Prepare the SQL statement
        $this->query($sql);
        
        // Bind the value to the parameter and execute
        $this->execute(['value' => $value]);

        // Fetch the result
        return $this->statement->fetch();
        
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
}