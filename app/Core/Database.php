<?php

namespace Core;

use PDO;
use Exception;
/**
 * Class Database
 *
 * Handles database connection and basic query execution using PDO.
 * Provides methods for preparing, executing queries, and inserting data.
 */
class Database {

    /**
     * PDO connection instance.
     * @var PDO
     */
    private $connection;

    /**
     * PDO statement instance.
     * @var \PDOStatement|null
     */
    private $statement;

    /**
     * Create a new database connection.
     * @param array $dbconfig
     * @param string $user
     * @param string $password
     * @throws \RuntimeException
     */
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

    /**
     * Prepare a SQL query.
     * @param string $query
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function query($query) {
        if (empty($query)) {
            throw new \InvalidArgumentException("Query cannot be empty.", 500);
        }
        $this->statement = $this->connection->prepare($query);
        return $this;
    }

    /**
     * Execute the prepared statement with parameters.
     * @param array $param
     * @return $this
     * @throws \RuntimeException|\InvalidArgumentException
     */
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

    /**
     * Insert data into a table.
     * @param string $table
     * @param array $data
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function insert(string $table, array $data): self {
        if (empty($table) || empty($data)) {
            throw new \InvalidArgumentException("Table name and data cannot be empty.");
        }
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
        return $this->query($sql)->execute($data);
    }

    /**
     * Fetch all results from the last executed query.
     * @return array
     * @throws \RuntimeException
     */
    public function findAll() {
        if (!$this->statement) {
            throw new \RuntimeException("No query has been executed.", 500);
        }

        // Check if the statement is prepared
        if (!$this->statement instanceof \PDOStatement) {
            throw new \RuntimeException("Query has not been prepared.", 500);
        }

        // Check if the statement is executed
        if (!$this->statement->execute()) {
            throw new \RuntimeException("Query execution failed.", 500);
        }

        // Fetch all results
        return $this->statement->fetchAll() ?? null;
    }

    // find a single record with id or slug
    public function find(string $table, string $identifier): array | bool {
        if (empty($table) || empty($identifier)) {
            throw new \InvalidArgumentException("Table and identifier cannot be empty.");
        }

        // Determine if identifier is numeric (ID) or name
        $column = ctype_digit($identifier) ? "id" : "url";
        
        $sql = "SELECT * FROM `{$table}` WHERE `{$column}` = :{$column} LIMIT 1";
        
        $this->query($sql)->execute([$column => $identifier]);
        
        return $this->fetch();
    }

    public function select(string $table, array $columns = ["*"], array $conditions = []): array {
        
        if (empty($table)) {
            throw new \InvalidArgumentException("Table name cannot be empty.");
        }

        // Handle asterisk specially - don't quote it
        if ($columns === ["*"] || (count($columns) === 1 && $columns[0] === "*")) {
            $columnsSql = "*";
        } else {
            $columnsSql = implode(", ", array_map(fn($col) => "`{$col}`", $columns));
        }

        if (empty($conditions)) {
            $sql = "SELECT {$columnsSql} FROM `{$table}`";
        } else {
            $whereClause = implode(" AND ", array_map(fn($key) => "`{$key}` = :{$key}", array_keys($conditions)));
            
            $sql = "SELECT {$columnsSql} FROM `{$table}` WHERE {$whereClause}";
        }
        
        $this->query($sql)->execute($conditions);

        return $this->fetchAll() ?? [];
    }

    public function fetch() {
        
        if (!$this->statement) {
            throw new \RuntimeException("No query has been executed.", 500);
        }
        
        // Check if the statement is prepared
        if (!$this->statement instanceof \PDOStatement) {
            throw new \RuntimeException("Query has not been prepared.", 500);
        }

        return $this->statement->fetch();
    }

    public function fetchAll() {
        
        // check query execution
        if (!$this->statement) {
            throw new \RuntimeException("No query has been executed.", 500);
        }

        // Check statement prepared
        
        if (!$this->statement instanceof \PDOStatement) {
            throw new \RuntimeException("Query has not been prepared.", 500);
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

    // transaction support

    public function beginTransaction(): bool {
        try {
            if ($this->connection->inTransaction()) {
                throw new \RuntimeException("Transaction already active");
            }
            return $this->connection->beginTransaction();
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to start transaction: " . $e->getMessage());
        }
    }

    public function commit(): bool {
        try {
            if (!$this->connection->inTransaction()) {
                throw new \RuntimeException("No active transaction to commit");
            }
            return $this->connection->commit();
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to commit transaction: " . $e->getMessage());
        }
    }

    public function rollBack(): bool {
        if(!$this->inTransaction()) {
            throw new \RuntimeException("Cannot roll back a transaction that is not active.");
        }
        try { 
            return $this->connection->rollBack();
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to rollback transaction: " . $e->getMessage());
        }
    }

    public function inTransaction(): bool {
        return $this->connection->inTransaction();
    }
}