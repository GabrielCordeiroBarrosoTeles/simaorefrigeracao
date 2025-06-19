<?php

namespace DataAccess\Database;

use PDO;
use PDOException;

class MySQLDatabase implements DatabaseInterface
{
    private ?PDO $connection = null;
    
    public function __construct(
        private string $host,
        private string $database,
        private string $username,
        private string $password
    ) {}
    
    public function connect(): PDO
    {
        if ($this->connection === null) {
            try {
                $this->connection = new PDO(
                    "mysql:host={$this->host};dbname={$this->database};charset=utf8",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                throw new \Exception("Database connection failed: " . $e->getMessage());
            }
        }
        
        return $this->connection;
    }
    
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->connect()->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function lastInsertId(): string
    {
        return $this->connect()->lastInsertId();
    }
    
    public function beginTransaction(): bool
    {
        return $this->connect()->beginTransaction();
    }
    
    public function commit(): bool
    {
        return $this->connect()->commit();
    }
    
    public function rollback(): bool
    {
        return $this->connect()->rollBack();
    }
}