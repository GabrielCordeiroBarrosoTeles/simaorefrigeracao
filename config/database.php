<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'friocerto';
    private $username = 'root';
    private $password = '';
    private $conn;
    
    // Método para conectar ao banco de dados
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo "Erro na conexão: " . $e->getMessage();
        }
        
        return $this->conn;
    }
}

// Função para obter conexão com o banco de dados
function db_connect() {
    $database = new Database();
    return $database->getConnection();
}
