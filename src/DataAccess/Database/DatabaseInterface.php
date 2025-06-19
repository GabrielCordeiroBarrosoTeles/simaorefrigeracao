<?php

namespace DataAccess\Database;

interface DatabaseInterface
{
    public function connect(): mixed;
    public function query(string $sql, array $params = []): array;
    public function execute(string $sql, array $params = []): bool;
    public function lastInsertId(): string;
    public function beginTransaction(): bool;
    public function commit(): bool;
    public function rollback(): bool;
}