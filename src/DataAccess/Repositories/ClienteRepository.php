<?php

namespace DataAccess\Repositories;

use DataAccess\Database\DatabaseInterface;

class ClienteRepository implements RepositoryInterface
{
    public function __construct(private DatabaseInterface $database) {}
    
    public function findById(int $id): ?array
    {
        $result = $this->database->query(
            "SELECT * FROM clientes WHERE id = ?", 
            [$id]
        );
        return $result[0] ?? null;
    }
    
    public function findAll(): array
    {
        return $this->database->query("SELECT * FROM clientes ORDER BY nome");
    }
    
    public function create(array $data): int
    {
        $this->database->execute(
            "INSERT INTO clientes (nome, email, telefone, endereco, tipo) VALUES (?, ?, ?, ?, ?)",
            [$data['nome'], $data['email'], $data['telefone'], $data['endereco'], $data['tipo']]
        );
        return (int) $this->database->lastInsertId();
    }
    
    public function update(int $id, array $data): bool
    {
        return $this->database->execute(
            "UPDATE clientes SET nome = ?, email = ?, telefone = ?, endereco = ?, tipo = ? WHERE id = ?",
            [$data['nome'], $data['email'], $data['telefone'], $data['endereco'], $data['tipo'], $id]
        );
    }
    
    public function delete(int $id): bool
    {
        return $this->database->execute("DELETE FROM clientes WHERE id = ?", [$id]);
    }
}