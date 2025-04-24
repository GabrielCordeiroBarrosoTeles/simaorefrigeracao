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
          'ID de técnico inválido.');
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
}
