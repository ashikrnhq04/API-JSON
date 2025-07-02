<?php

namespace src\Core;

use PDO;

class Database {

    private $connection;

    private $statement; 

        
    public function __construct($dbconfig, $user = 'root', $password = '') {
        
            $dsn = "mysql:" . http_build_query($dbconfig, "", ";");
            
            $this->connection = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        
    }

    public function createTable($tableName, $columns) {
        if (empty($tableName) || empty($columns)) {
            throw new \InvalidArgumentException("Table name and columns cannot be empty.");
        }
        if (!is_string($tableName) || !is_array($columns)) {
            throw new \InvalidArgumentException("Invalid table name or columns format.");
        }
        // Sanitize table name
        $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
        if (empty($tableName)) {
            throw new \InvalidArgumentException("Invalid table name.");
        }
        // Sanitize column names
        $columns = array_map(function($col) {
            return preg_replace('/[^a-zA-Z0-9_]/', '', $col);
        }, $columns);

        $columnsSql = implode(", ", array_map(function($col) {
            return "`$col` VARCHAR(255)";
        }, $columns));

        $query = "CREATE TABLE IF NOT EXISTS `$tableName` ($columnsSql)";
        
        $this->connection->exec($query);
    }

    public function query($query, $params = []) {
        $this->statement = $this->connection->prepare($query);

        $this->statement->execute($params);

        return $this; 
    }

    public function get() {
        return $this->statement->fetchAll();
    }

}