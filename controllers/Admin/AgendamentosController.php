<?php
class AgendamentosController {
    private $db;
    
    public function __construct() {
        // Verificar se o usuário está logado
        if (!is_logged_in()) {
            redirect('/admin/login');
        }
        
        $this->db = db_connect();
    }
    
    public function index() {
        // Buscar todos os agendamentos
        $query = "SELECT a.*, c.nome as cliente_nome, s.titulo as servico_nome, t.nome as tecnico_nome
                  FROM agendamentos a
                  LEFT JOIN clientes c ON a.cliente_id = c.id
                  LEFT JOIN servicos s ON a.servico_id = s.id
                  LEFT JOIN tecnicos t ON a.tecnico_id = t.id
                  ORDER BY a.data_agendamento DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require 'views/admin/agendamentos/index.php';
    }
    
    public function calendario() {
        // Buscar todos os agendamentos para o calendário
        $query = "SELECT a.id, a.titulo, a.data_agendamento, a.hora_inicio, a.hora_fim, 
                  a.status, c.nome as cliente_nome, t.nome as tecnico_nome, t.cor as tecnico_cor
                  FROM agendamentos a
                  LEFT JOIN clientes c ON a.cliente_id = c.id
                  LEFT JOIN tecnicos t ON a.tecnico_id = t.id
                  ORDER BY a.data_agendamento ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatar agendamentos para o calendário
        $eventos = [];
        foreach ($agendamentos as $agendamento) {
            $data_inicio = $agendamento['data_agendamento'] . ' ' . $agendamento['hora_inicio'];
            $data_fim = $agendamento['data_agendamento'] . ' ' . $agendamento['hora_fim'];
            
            // Definir cor com base no status
            $cor = '#3b82f6'; // Azul padrão
            switch ($agendamento['status']) {
                case 'concluido':
                    $cor = '#10b981'; // Verde
                    break;
                case 'cancelado':
                    $cor = '#ef4444'; // Vermelho
                    break;
                case 'pendente':
                    $cor = '#f59e0b'; // Laranja
                    break;
            }
            
            // Usar a cor do técnico se disponível
            if (!empty($agendamento['tecnico_cor'])) {
                $cor = $agendamento['tecnico_cor'];
            }
            
            $eventos[] = [
                'id' => $agendamento['id'],
                'title' => $agendamento['titulo'],
                'start' => $data_inicio,
                'end' => $data_fim,
                'color' => $cor,
                'extendedProps' => [
                    'cliente' => $agendamento['cliente_nome'],
                    'tecnico' => $agendamento['tecnico_nome'],
                    'status' => $agendamento['status']
                ]
            ];
        }
        
        // Buscar clientes para o formulário
        $query = "SELECT id, nome FROM clientes ORDER BY nome ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar serviços para o formulário
        $query = "SELECT id, titulo FROM servicos ORDER BY titulo ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar técnicos para o formulário
        $query = "SELECT id, nome FROM tecnicos ORDER BY nome ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require 'views/admin/agendamentos/calendario.php';
    }
    
    public function create() {
        // Buscar clientes para o formulário
        $query = "SELECT id, nome FROM clientes ORDER BY nome ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar serviços para o formulário
        $query = "SELECT id, titulo FROM servicos ORDER BY titulo ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar técnicos para o formulário
        $query = "SELECT id, nome FROM tecnicos ORDER BY nome ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require 'views/admin/agendamentos/create.php';
    }
    
    public function store() {
        if (!is_post_request()) {
            redirect('/admin/agendamentos');
        }
        
        // Verificar CSRF token
        if (!verify_csrf_token($_POST['csrf_token'])) {
            set_flash_message('danger', 'Erro de validação. Por favor, tente novamente.');
            redirect('/admin/agendamentos/novo');
        }
        
        // Sanitizar e validar dados
        $titulo = sanitize($_POST['titulo'] ?? '');
        $cliente_id = (int)($_POST['cliente_id'] ?? 0);
        $servico_id = (int)($_POST['servico_id'] ?? 0);
        $tecnico_id = (int)($_POST['tecnico_id'] ?? 0);
        $data_agendamento = sanitize($_POST['data_agendamento'] ?? '');
        $hora_inicio = sanitize($_POST['hora_inicio'] ?? '');
        $hora_fim = sanitize($_POST['hora_fim'] ?? '');
        $observacoes = sanitize($_POST['observacoes'] ?? '');
        $status = sanitize($_POST['status'] ?? 'pendente');
        
        // Validação básica
        if (empty($titulo) || empty($data_agendamento) || empty($hora_inicio) || $cliente_id <= 0 || $servico_id <= 0 || $tecnico_id <= 0) {
            set_flash_message('danger', 'Por favor, preencha todos os campos obrigatórios.');
            redirect('/admin/agendamentos/novo');
        }
        
        try {
            $query = "INSERT INTO agendamentos (titulo, cliente_id, servico_id, tecnico_id, data_agendamento, 
                      hora_inicio, hora_fim, observacoes, status, data_criacao) 
                      VALUES (:titulo, :cliente_id, :servico_id, :tecnico_id, :data_agendamento, 
                      :hora_inicio, :hora_fim, :observacoes, :status, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':cliente_id', $cliente_id);
            $stmt->bindParam(':servico_id', $servico_id);
            $stmt->bindParam(':tecnico_id', $tecnico_id);
            $stmt->bindParam(':data_agendamento', $data_agendamento);
            $stmt->bindParam(':hora_inicio', $hora_inicio);
            $stmt->bindParam(':hora_fim', $hora_fim);
            $stmt->bindParam(':observacoes', $observacoes);
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Agendamento adicionado com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao adicionar agendamento. Por favor, tente novamente.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação. Por favor, tente novamente.');
            
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/admin/agendamentos');
    }
    
    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            set_flash_message('danger', 'ID de agendamento inválido.');
            redirect('/admin/agendamentos');
        }
        
        // Buscar agendamento pelo ID
        $query = "SELECT * FROM agendamentos WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$agendamento) {
            set_flash_message('danger', 'Agendamento não encontrado.');
            redirect('/admin/agendamentos');
        }
        
        // Buscar clientes para o formulário
        $query = "SELECT id, nome FROM clientes ORDER BY nome ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar serviços para o formulário
        $query = "SELECT id, titulo FROM servicos ORDER BY titulo ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar técnicos para o formulário
        $query = "SELECT id, nome FROM tecnicos ORDER BY nome ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require 'views/admin/agendamentos/edit.php';
    }
    
    public function update() {
        if (!is_post_request()) {
            redirect('/admin/agendamentos');
        }
        
        // Verificar CSRF token
        if (!verify_csrf_token($_POST['csrf_token'])) {
            set_flash_message('danger', 'Erro de validação. Por favor, tente novamente.');
            redirect('/admin/agendamentos');
        }
        
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($id <= 0) {
            set_flash_message('danger', 'ID de agendamento inválido.');
            redirect('/admin/agendamentos');
        }
        
        // Sanitizar e validar dados
        $titulo = sanitize($_POST['titulo'] ?? '');
        $cliente_id = (int)($_POST['cliente_id'] ?? 0);
        $servico_id = (int)($_POST['servico_id'] ?? 0);
        $tecnico_id = (int)($_POST['tecnico_id'] ?? 0);
        $data_agendamento = sanitize($_POST['data_agendamento'] ?? '');
        $hora_inicio = sanitize($_POST['hora_inicio'] ?? '');
        $hora_fim = sanitize($_POST['hora_fim'] ?? '');
        $observacoes = sanitize($_POST['observacoes'] ?? '');
        $status = sanitize($_POST['status'] ?? 'pendente');
        
        // Validação básica
        if (empty($titulo) || empty($data_agendamento) || empty($hora_inicio) || $cliente_id <= 0 || $servico_id <= 0 || $tecnico_id <= 0) {
            set_flash_message('danger', 'Por favor, preencha todos os campos obrigatórios.');
            redirect('/admin/agendamentos/editar?id=' . $id);
        }
        
        try {
            $query = "UPDATE agendamentos 
                      SET titulo = :titulo, cliente_id = :cliente_id, servico_id = :servico_id, 
                      tecnico_id = :tecnico_id, data_agendamento = :data_agendamento, 
                      hora_inicio = :hora_inicio, hora_fim = :hora_fim, 
                      observacoes = :observacoes, status = :status, data_atualizacao = NOW() 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':cliente_id', $cliente_id);
            $stmt->bindParam(':servico_id', $servico_id);
            $stmt->bindParam(':tecnico_id', $tecnico_id);
            $stmt->bindParam(':data_agendamento', $data_agendamento);
            $stmt->bindParam(':hora_inicio', $hora_inicio);
            $stmt->bindParam(':hora_fim', $hora_fim);
            $stmt->bindParam(':observacoes', $observacoes);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Agendamento atualizado com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao atualizar agendamento. Por favor, tente novamente.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação. Por favor, tente novamente.');
            
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/admin/agendamentos');
    }
    
    public function delete() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            set_flash_message('danger', 'ID de agendamento inválido.');
            redirect('/admin/agendamentos');
        }
        
        try {
            $query = "DELETE FROM agendamentos WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Agendamento excluído com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao excluir agendamento. Por favor, tente novamente.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação. Por favor, tente novamente.');
            
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/admin/agendamentos');
    }
    
    // API para o calendário
    public function api() {
        header('Content-Type: application/json');
        
        if (!is_logged_in()) {
            echo json_encode(['error' => 'Não autorizado']);
            exit;
        }
        
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        
        switch ($action) {
            case 'get':
                $this->apiGetAgendamentos();
                break;
            case 'add':
                $this->apiAddAgendamento();
                break;
            case 'update':
                $this->apiUpdateAgendamento();
                break;
            case 'delete':
                $this->apiDeleteAgendamento();
                break;
            default:
                echo json_encode(['error' => 'Ação inválida']);
                break;
        }
    }
    
    private function apiGetAgendamentos() {
        $start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d');
        $end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d', strtotime('+30 days'));
        
        $query = "SELECT a.id, a.titulo, a.data_agendamento, a.hora_inicio, a.hora_fim, 
                  a.status, c.nome as cliente_nome, t.nome as tecnico_nome, t.cor as tecnico_cor
                  FROM agendamentos a
                  LEFT JOIN clientes c ON a.cliente_id = c.id
                  LEFT JOIN tecnicos t ON a.tecnico_id = t.id
                  WHERE a.data_agendamento BETWEEN :start AND :end
                  ORDER BY a.data_agendamento ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end', $end);
        $stmt->execute();
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $eventos = [];
        foreach ($agendamentos as $agendamento) {
            $data_inicio = $agendamento['data_agendamento'] . ' ' . $agendamento['hora_inicio'];
            $data_fim = $agendamento['data_agendamento'] . ' ' . $agendamento['hora_fim'];
            
            // Definir cor com base no status
            $cor = '#3b82f6'; // Azul padrão
            switch ($agendamento['status']) {
                case 'concluido':
                    $cor = '#10b981'; // Verde
                    break;
                case 'cancelado':
                    $cor = '#ef4444'; // Vermelho
                    break;
                case 'pendente':
                    $cor = '#f59e0b'; // Laranja
                    break;
            }
            
            // Usar a cor do técnico se disponível
            if (!empty($agendamento['tecnico_cor'])) {
                $cor = $agendamento['tecnico_cor'];
            }
            
            $eventos[] = [
                'id' => $agendamento['id'],
                'title' => $agendamento['titulo'],
                'start' => $data_inicio,
                'end' => $data_fim,
                'color' => $cor,
                'extendedProps' => [
                    'cliente' => $agendamento['cliente_nome'],
                    'tecnico' => $agendamento['tecnico_nome'],
                    'status' => $agendamento['status']
                ]
            ];
        }
        
        echo json_encode($eventos);
    }
    
    private function apiAddAgendamento() {
        // Implementação para adicionar agendamento via API
    }
    
    private function apiUpdateAgendamento() {
        // Implementação para atualizar agendamento via API
    }
    
    private function apiDeleteAgendamento() {
        // Implementação para excluir agendamento via API
    }
}
