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
        try {
            // Verificar conexão com o banco
            if (!$this->db) {
                error_log("Erro: Conexão com o banco de dados falhou em AgendamentosController::index()");
                set_flash_message('danger', 'Erro de conexão com o banco de dados.');
                require 'views/admin/agendamentos/index.php';
                return;
            }
            
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
            
            // Verificar se há agendamentos
            if (empty($agendamentos)) {
                error_log("Aviso: Nenhum agendamento encontrado em AgendamentosController::index()");
            }
            
            require 'views/admin/agendamentos/index.php';
        } catch (PDOException $e) {
            error_log("Erro PDO em AgendamentosController::index(): " . $e->getMessage());
            set_flash_message('danger', 'Erro ao buscar agendamentos: ' . $e->getMessage());
            $agendamentos = [];
            require 'views/admin/agendamentos/index.php';
        } catch (Exception $e) {
            error_log("Erro geral em AgendamentosController::index(): " . $e->getMessage());
            set_flash_message('danger', 'Erro inesperado: ' . $e->getMessage());
            $agendamentos = [];
            require 'views/admin/agendamentos/index.php';
        }
    }
    
    public function calendario() {
        // Buscar todos os agendamentos para o calendário
        $query = "SELECT a.id, a.titulo, a.data_agendamento, a.hora_inicio, a.hora_fim, a.observacoes,
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
            $data_fim = $agendamento['data_agendamento'] . ' ' . ($agendamento['hora_fim'] ?? '23:59:59');
            
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
                    'status' => $agendamento['status'],
                    'observacoes' => $agendamento['observacoes']
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
        $tecnico_id = isset($_GET['tecnico_id']) ? (int)$_GET['tecnico_id'] : null;
        $status = isset($_GET['status']) ? explode(',', $_GET['status']) : null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'desc';
        
        // Construir a consulta base
        $query = "SELECT a.id, a.titulo, a.data_agendamento, a.hora_inicio, a.hora_fim, a.observacoes,
                  a.status, c.nome as cliente_nome, t.nome as tecnico_nome, t.cor as tecnico_cor,
                  t.id as tecnico_id
                  FROM agendamentos a
                  LEFT JOIN clientes c ON a.cliente_id = c.id
                  LEFT JOIN tecnicos t ON a.tecnico_id = t.id
                  WHERE 1=1";
        
        $params = [];
        
        // Adicionar filtros
        if ($start) {
            $query .= " AND a.data_agendamento >= :start";
            $params[':start'] = $start;
        }
        
        if ($end) {
            $query .= " AND a.data_agendamento <= :end";
            $params[':end'] = $end;
        }
        
        if ($tecnico_id) {
            $query .= " AND a.tecnico_id = :tecnico_id";
            $params[':tecnico_id'] = $tecnico_id;
        }
        
        if ($status && is_array($status)) {
            $placeholders = [];
            foreach ($status as $i => $s) {
                $key = ":status{$i}";
                $placeholders[] = $key;
                $params[$key] = $s;
            }
            if (!empty($placeholders)) {
                $query .= " AND a.status IN (" . implode(',', $placeholders) . ")";
            }
        }
        
        // Ordenação
        $query .= " ORDER BY a.data_agendamento " . ($sort === 'asc' ? 'ASC' : 'DESC');
        
        // Limite
        if ($limit) {
            $query .= " LIMIT :limit";
            $params[':limit'] = $limit;
        }
        
        try {
            $stmt = $this->db->prepare($query);
            
            // Bind params
            foreach ($params as $key => $value) {
                if ($key === ':limit') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            $stmt->execute();
            $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $eventos = [];
            foreach ($agendamentos as $agendamento) {
                $data_inicio = $agendamento['data_agendamento'] . ' ' . $agendamento['hora_inicio'];
                $data_fim = $agendamento['data_agendamento'] . ' ' . ($agendamento['hora_fim'] ?? '23:59:59');
                
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
                        'tecnico_id' => $agendamento['tecnico_id'],
                        'status' => $agendamento['status'],
                        'observacoes' => $agendamento['observacoes']
                    ]
                ];
            }
            
            echo json_encode($eventos);
        } catch (PDOException $e) {
            echo json_encode([
                'error' => 'Erro ao buscar agendamentos',
                'message' => DEBUG_MODE ? $e->getMessage() : null
            ]);
        }
    }
    
    private function apiAddAgendamento() {
        // Verificar se é uma requisição POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Método não permitido']);
            exit;
        }
        
        // Obter dados do corpo da requisição
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            echo json_encode(['error' => 'Dados inválidos']);
            exit;
        }
        
        // Sanitizar e validar dados
        $titulo = sanitize($data['titulo'] ?? '');
        $cliente_id = (int)($data['cliente_id'] ?? 0);
        $servico_id = (int)($data['servico_id'] ?? 0);
        $tecnico_id = (int)($data['tecnico_id'] ?? 0);
        $data_agendamento = sanitize($data['data_agendamento'] ?? '');
        $hora_inicio = sanitize($data['hora_inicio'] ?? '');
        $hora_fim = sanitize($data['hora_fim'] ?? '');
        $observacoes = sanitize($data['observacoes'] ?? '');
        $status = sanitize($data['status'] ?? 'pendente');
        
        // Validação básica
        if (empty($titulo) || empty($data_agendamento) || empty($hora_inicio) || $cliente_id <= 0 || $servico_id <= 0 || $tecnico_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Por favor, preencha todos os campos obrigatórios.'
            ]);
            exit;
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
                $id = $this->db->lastInsertId();
                echo json_encode([
                    'success' => true,
                    'message' => 'Agendamento adicionado com sucesso!',
                    'id' => $id
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao adicionar agendamento.'
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao processar sua solicitação.',
                'error' => DEBUG_MODE ? $e->getMessage() : null
            ]);
        }
    }
    
    private function apiUpdateAgendamento() {
        // Verificar se é uma requisição POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Método não permitido']);
            exit;
        }
        
        // Obter dados do corpo da requisição
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['id'])) {
            echo json_encode(['error' => 'Dados inválidos']);
            exit;
        }
        
        $id = (int)$data['id'];
        
        // Se apenas o status está sendo atualizado
        if (isset($data['status']) && count($data) === 2) {
            $status = sanitize($data['status']);
            
            try {
                $query = "UPDATE agendamentos SET status = :status, data_atualizacao = NOW() WHERE id = :id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Status atualizado com sucesso!'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Erro ao atualizar status.'
                    ]);
                }
            } catch (PDOException $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao processar sua solicitação.',
                    'error' => DEBUG_MODE ? $e->getMessage() : null
                ]);
            }
            
            exit;
        }
        
        // Atualização completa do agendamento
        $titulo = sanitize($data['titulo'] ?? '');
        $cliente_id = (int)($data['cliente_id'] ?? 0);
        $servico_id = (int)($data['servico_id'] ?? 0);
        $tecnico_id = (int)($data['tecnico_id'] ?? 0);
        $data_agendamento = sanitize($data['data_agendamento'] ?? '');
        $hora_inicio = sanitize($data['hora_inicio'] ?? '');
        $hora_fim = sanitize($data['hora_fim'] ?? '');
        $observacoes = sanitize($data['observacoes'] ?? '');
        $status = sanitize($data['status'] ?? 'pendente');
        
        // Validação básica
        if (empty($titulo) || empty($data_agendamento) || empty($hora_inicio) || $cliente_id <= 0 || $servico_id <= 0 || $tecnico_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Por favor, preencha todos os campos obrigatórios.'
            ]);
            exit;
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
                echo json_encode([
                    'success' => true,
                    'message' => 'Agendamento atualizado com sucesso!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao atualizar agendamento.'
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao processar sua solicitação.',
                'error' => DEBUG_MODE ? $e->getMessage() : null
            ]);
        }
    }
    
    private function apiDeleteAgendamento() {
        // Verificar se é uma requisição POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Método não permitido']);
            exit;
        }
        
        // Obter dados do corpo da requisição
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['id'])) {
            echo json_encode(['error' => 'Dados inválidos']);
            exit;
        }
        
        $id = (int)$data['id'];
        
        try {
            $query = "DELETE FROM agendamentos WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Agendamento excluído com sucesso!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao excluir agendamento.'
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao processar sua solicitação.',
                'error' => DEBUG_MODE ? $e->getMessage() : null
            ]);
        }
    }
}
