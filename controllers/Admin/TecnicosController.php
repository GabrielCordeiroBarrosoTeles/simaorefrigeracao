<?php
class TecnicosController {
    private $db;
    
    public function __construct() {
        // Verificar se o usuário está logado
        if (!is_logged_in()) {
            redirect('/admin/login');
        }
        
        $this->db = db_connect();
    }
    
    public function index() {
        // Buscar todos os técnicos
        $query = "SELECT * FROM tecnicos ORDER BY nome ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require 'views/admin/tecnicos/index.php';
    }
    
    public function create() {
        require 'views/admin/tecnicos/create.php';
    }
    
    public function store() {
        if (!is_post_request()) {
            redirect('/admin/tecnicos');
        }
        
        // Verificar CSRF token
        if (!verify_csrf_token($_POST['csrf_token'])) {
            set_flash_message('danger', 'Erro de validação. Por favor, tente novamente.');
            redirect('/admin/tecnicos/novo');
        }
        
        // Sanitizar e validar dados
        $nome = sanitize($_POST['nome'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $telefone = sanitize($_POST['telefone'] ?? '');
        $especialidade = sanitize($_POST['especialidade'] ?? '');
        $cor = sanitize($_POST['cor'] ?? '#3b82f6');
        $status = sanitize($_POST['status'] ?? 'ativo');
        
        // Validação básica
        if (empty($nome) || empty($email) || empty($telefone)) {
            set_flash_message('danger', 'Por favor, preencha todos os campos obrigatórios.');
            redirect('/admin/tecnicos/novo');
        }
        
        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash_message('danger', 'Por favor, informe um email válido.');
            redirect('/admin/tecnicos/novo');
        }
        
        try {
            // Verificar se o email já existe
            $query = "SELECT COUNT(*) as total FROM tecnicos WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                set_flash_message('danger', 'Este email já está cadastrado para outro técnico.');
                redirect('/admin/tecnicos/novo');
            }
            
            // Inserir novo técnico
            $query = "INSERT INTO tecnicos (nome, email, telefone, especialidade, cor, status, data_criacao) 
                      VALUES (:nome, :email, :telefone, :especialidade, :cor, :status, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':especialidade', $especialidade);
            $stmt->bindParam(':cor', $cor);
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Técnico adicionado com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao adicionar técnico. Por favor, tente novamente.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação. Por favor, tente novamente.');
            
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/admin/tecnicos');
    }
    
    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            set_flash_message('danger', 'ID de técnico inválido.');
            redirect('/admin/tecnicos');
        }
        
        // Buscar técnico pelo ID
        $query = "SELECT * FROM tecnicos WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tecnico) {
            set_flash_message('danger', 'Técnico não encontrado.');
            redirect('/admin/tecnicos');
        }
        
        require 'views/admin/tecnicos/edit.php';
    }
    
    public function update() {
        if (!is_post_request()) {
            redirect('/admin/tecnicos');
        }
        
        // Verificar CSRF token
        if (!verify_csrf_token($_POST['csrf_token'])) {
            set_flash_message('danger', 'Erro de validação. Por favor, tente novamente.');
            redirect('/admin/tecnicos');
        }
        
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($id <= 0) {
            set_flash_message('danger', 'ID de técnico inválido.');
            redirect('/admin/tecnicos');
        }
        
        // Sanitizar e validar dados
        $nome = sanitize($_POST['nome'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $telefone = sanitize($_POST['telefone'] ?? '');
        $especialidade = sanitize($_POST['especialidade'] ?? '');
        $cor = sanitize($_POST['cor'] ?? '#3b82f6');
        $status = sanitize($_POST['status'] ?? 'ativo');
        
        // Validação básica
        if (empty($nome) || empty($email) || empty($telefone)) {
            set_flash_message('danger', 'Por favor, preencha todos os campos obrigatórios.');
            redirect('/admin/tecnicos/editar?id=' . $id);
        }
        
        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash_message('danger', 'Por favor, informe um email válido.');
            redirect('/admin/tecnicos/editar?id=' . $id);
        }
        
        try {
            // Verificar se o email já existe para outro técnico
            $query = "SELECT COUNT(*) as total FROM tecnicos WHERE email = :email AND id != :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                set_flash_message('danger', 'Este email já está cadastrado para outro técnico.');
                redirect('/admin/tecnicos/editar?id=' . $id);
            }
            
            // Atualizar técnico
            $query = "UPDATE tecnicos 
                      SET nome = :nome, email = :email, telefone = :telefone, 
                      especialidade = :especialidade, cor = :cor, status = :status, 
                      data_atualizacao = NOW() 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':especialidade', $especialidade);
            $stmt->bindParam(':cor', $cor);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Técnico atualizado com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao atualizar técnico. Por favor, tente novamente.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação. Por favor, tente novamente.');
            
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/admin/tecnicos');
    }
    
    public function delete() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            set_flash_message('danger', 'ID de técnico inválido.');
            redirect('/admin/tecnicos');
        }
        
        try {
            // Verificar se o técnico está associado a algum agendamento
            $query = "SELECT COUNT(*) as total FROM agendamentos WHERE tecnico_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                set_flash_message('danger', 'Este técnico não pode ser excluído pois está associado a agendamentos.');
                redirect('/admin/tecnicos');
            }
            
            // Excluir técnico
            $query = "DELETE FROM tecnicos WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Técnico excluído com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao excluir técnico. Por favor, tente novamente.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação. Por favor, tente novamente.');
            
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/admin/tecnicos');
    }
    
    public function agendamentos() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            set_flash_message('danger', 'ID de técnico inválido.');
            redirect('/admin/tecnicos');
        }
        
        // Buscar técnico pelo ID
        $query = "SELECT * FROM tecnicos WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tecnico) {
            set_flash_message('danger', 'Técnico não encontrado.');
            redirect('/admin/tecnicos');
        }
        
        // Buscar agendamentos do técnico
        $query = "SELECT a.*, c.nome as cliente_nome, s.titulo as servico_nome
                  FROM agendamentos a
                  LEFT JOIN clientes c ON a.cliente_id = c.id
                  LEFT JOIN servicos s ON a.servico_id = s.id
                  WHERE a.tecnico_id = :tecnico_id
                  ORDER BY a.data_agendamento DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tecnico_id', $id);
        $stmt->execute();
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require 'views/admin/tecnicos/agendamentos.php';
    }
    
    // API para estatísticas de técnicos
    public function api() {
        header('Content-Type: application/json');
        
        if (!is_logged_in()) {
            echo json_encode(['error' => 'Não autorizado']);
            exit;
        }
        
          {
            echo json_encode(['error' => 'Não autorizado']);
            exit;
        }
        
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        
        switch ($action) {
            case 'stats':
                $this->apiGetTecnicoStats();
                break;
            case 'disponibilidade':
                $this->apiGetTecnicoDisponibilidade();
                break;
            default:
                echo json_encode(['error' => 'Ação inválida']);
                break;
        }
    }
    
    private function apiGetTecnicoStats() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            echo json_encode(['error' => 'ID de técnico inválido']);
            exit;
        }
        
        try {
            // Total de agendamentos
            $query = "SELECT COUNT(*) as total FROM agendamentos WHERE tecnico_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Total de agendamentos concluídos
            $query = "SELECT COUNT(*) as total FROM agendamentos WHERE tecnico_id = :id AND status = 'concluido'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $concluidos = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Total de agendamentos pendentes
            $query = "SELECT COUNT(*) as total FROM agendamentos WHERE tecnico_id = :id AND status = 'pendente'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $pendentes = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Total de agendamentos cancelados
            $query = "SELECT COUNT(*) as total FROM agendamentos WHERE tecnico_id = :id AND status = 'cancelado'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $cancelados = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            echo json_encode([
                'success' => true,
                'total' => $total,
                'concluidos' => $concluidos,
                'pendentes' => $pendentes,
                'cancelados' => $cancelados
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'error' => 'Erro ao buscar estatísticas',
                'message' => DEBUG_MODE ? $e->getMessage() : null
            ]);
        }
    }
    
    private function apiGetTecnicoDisponibilidade() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $data = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');
        
        if ($id <= 0) {
            echo json_encode(['error' => 'ID de técnico inválido']);
            exit;
        }
        
        try {
            // Buscar agendamentos do técnico na data especificada
            $query = "SELECT hora_inicio, hora_fim FROM agendamentos 
                      WHERE tecnico_id = :id AND data_agendamento = :data AND status != 'cancelado'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':data', $data);
            $stmt->execute();
            $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Horários ocupados
            $horarios_ocupados = [];
            foreach ($agendamentos as $agendamento) {
                $inicio = strtotime($agendamento['hora_inicio']);
                $fim = strtotime($agendamento['hora_fim'] ?? date('H:i:s', $inicio + 7200)); // 2 horas padrão se não tiver fim
                
                $horarios_ocupados[] = [
                    'inicio' => date('H:i', $inicio),
                    'fim' => date('H:i', $fim)
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $data,
                'horarios_ocupados' => $horarios_ocupados
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'error' => 'Erro ao buscar disponibilidade',
                'message' => DEBUG_MODE ? $e->getMessage() : null
            ]);
        }
    }
}
