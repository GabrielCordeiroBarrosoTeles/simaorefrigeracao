<?php
class HomeController {
    private $db;
    
    public function __construct() {
        $this->db = db_connect();
    }
    
    public function index() {
        // Buscar serviços do banco de dados
        $query = "SELECT * FROM servicos ORDER BY id ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar depoimentos do banco de dados
        $query = "SELECT * FROM depoimentos ORDER BY id DESC LIMIT 3";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $depoimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar estatísticas
        $query = "SELECT * FROM estatisticas LIMIT 4";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $estatisticas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar informações da empresa
        $query = "SELECT * FROM configuracoes WHERE id = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $configuracoes = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Carregar a view
        require 'views/home.php';
    }
}
