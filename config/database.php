<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
// Alterar a definição do nome do banco de dados de 'friocerto' para 'simaorefrigeracao'
define('DB_NAME', 'simaorefrigeracao');
define('DB_USER', 'root');
define('DB_PASS', '');

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;
    
    // Método para conectar ao banco de dados
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            // Registrar o erro em log em vez de exibi-lo
            error_log("Erro na conexão com o banco de dados: " . $e->getMessage());
            throw $e; // Relançar a exceção para ser tratada pelo chamador
        }
        
        return $this->conn;
    }
}

// Função para obter conexão com o banco de dados
function db_connect() {
    static $db = null;
    
    // Se já temos uma conexão, retorná-la (singleton pattern)
    if ($db !== null) {
        return $db;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    return $db;
}
