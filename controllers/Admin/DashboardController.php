<?php
class DashboardController {
    private $db;
    
    public function __construct() {
        // Verificar se o usuário está logado
        if (!is_logged_in()) {
            redirect('/admin/login');
        }
        
        $this->db = db_connect();
    }
    
    public function index() {
        // Estatísticas para o dashboard
        $stats = [
            'clientes' => $this->countClientes(),
            'servicos' => $this->countServicos(),
            'agendamentos' => $this->countAgendamentos(),
            'tecnicos' => $this->countTecnicos(),
            'agendamentos_hoje' => $this->countAgendamentosHoje(),
            'agendamentos_semana' => $this->countAgendamentosSemana(),
            'contatos_novos' => $this->countContatosNovos()
        ];
        
        // Agendamentos recentes
        $agendamentos_recentes = $this->getAgendamentosRecentes();
        
        // Próximos agendamentos
        $proximos_agendamentos = $this->getProximosAgendamentos();
        
        // Clientes recentes
        $clientes_recentes = $this->getClientesRecentes();
        
        require 'views/admin/dashboard.php';
    }
    
    private function countClientes() {
        $query = "SELECT COUNT(*) as total FROM clientes";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    private function countServicos() {
        $query = "SELECT COUNT(*) as total FROM servicos";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    private function countAgendamentos() {
        $query = "SELECT COUNT(*) as total FROM agendamentos";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    private function countTecnicos() {
        $query = "SELECT COUNT(*) as total FROM tecnicos";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    private function countAgendamentosHoje() {
        $query = "SELECT COUNT(*) as total FROM agendamentos WHERE DATE(data_agendamento) = CURDATE()";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    private function countAgendamentosSemana() {
        $query = "SELECT COUNT(*) as total FROM agendamentos 
                  WHERE YEARWEEK(data_agendamento, 1) = YEARWEEK(CURDATE(), 1)";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    private function countContatosNovos() {
        $query = "SELECT COUNT(*) as total FROM contatos WHERE status = 'novo'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    private function getAgendamentosRecentes() {
        $query = "SELECT a.*, c.nome as cliente_nome, s.titulo as servico_nome, t.nome as tecnico_nome
                  FROM agendamentos a
                  LEFT JOIN clientes c ON a.cliente_id = c.id
                  LEFT JOIN servicos s ON a.servico_id = s.id
                  LEFT JOIN tecnicos t ON a.tecnico_id = t.id
                  ORDER BY a.data_agendamento DESC
                  LIMIT 5";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getProximosAgendamentos() {
        $query = "SELECT a.*, c.nome as cliente_nome, s.titulo as servico_nome, t.nome as tecnico_nome
                  FROM agendamentos a
                  LEFT JOIN clientes c ON a.cliente_id = c.id
                  LEFT JOIN servicos s ON a.servico_id = s.id
                  LEFT JOIN tecnicos t ON a.tecnico_id = t.id
                  WHERE a.data_agendamento >= CURDATE()
                  ORDER BY a.data_agendamento ASC
                  LIMIT 5";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getClientesRecentes() {
        $query = "SELECT * FROM clientes ORDER BY data_criacao DESC LIMIT 5";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
