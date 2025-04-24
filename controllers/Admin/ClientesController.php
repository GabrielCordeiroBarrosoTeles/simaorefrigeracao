<?php
class ClientesController {
    private $db;
    
    public function __construct() {
        // Verificar se o usuário está logado
        if (!is_logged_in()) {
            redirect('/admin/login');
        }
        
        $this->db = db_connect();
    }
    
    public function index() {
        // Buscar todos os clientes
        $query = "SELECT * FROM clientes ORDER BY nome ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require 'views/admin/clientes/index.php';
    }
    
    public function create() {
        require 'views/admin/clientes/create.php';
    }
    
    public function store() {
        if (!is_post_request()) {
            redirect('/admin/clientes');
        }
        
        // Verificar CSRF token
        if (!verify_csrf_token($_POST['csrf_token'])) {
            set_flash_message('danger', 'Erro de validação. Por favor, tente novamente.');
            redirect('/admin/clientes/novo');
        }
        
        // Sanitizar e validar dados
        $nome = sanitize($_POST['nome'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $telefone = sanitize($_POST['telefone'] ?? '');
        $endereco = sanitize($_POST['endereco'] ?? '');
        $cidade = sanitize($_POST['cidade'] ?? '');
        $estado = sanitize($_POST['estado'] ?? '');
        $cep = sanitize($_POST['cep'] ?? '');
        $tipo = sanitize($_POST['tipo'] ?? 'residencial');
        $observacoes = sanitize($_POST['observacoes'] ?? '');
        
        // Validação básica
        if (empty($nome) || empty($email) || empty($telefone)) {
            set_flash_message('danger', 'Por favor, preencha todos os campos obrigatórios.');
            redirect('/admin/clientes/novo');
        }
        
        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash_message('danger', 'Por favor, informe um email válido.');
            redirect('/admin/clientes/novo');
        }
        
        try {
            // Verificar se o email já existe
            $query = "SELECT COUNT(*) as total FROM clientes WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                set_flash_message('danger', 'Este email já está cadastrado para outro cliente.');
                redirect('/admin/clientes/novo');
            }
            
            // Inserir novo cliente
            $query = "INSERT INTO clientes (nome, email, telefone, endereco, cidade, estado, cep, tipo, observacoes, data_criacao) 
                      VALUES (:nome, :email, :telefone, :endereco, :cidade, :estado, :cep, :tipo, :observacoes, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':endereco', $endereco);
            $stmt->bindParam(':cidade', $cidade);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':cep', $cep);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':observacoes', $observacoes);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Cliente adicionado com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao adicionar cliente. Por favor, tente novamente.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação. Por favor, tente novamente.');
            
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/admin/clientes');
    }
    
    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            set_flash_message('danger', 'ID de cliente inválido.');
            redirect('/admin/clientes');
        }
        
        // Buscar cliente pelo ID
        $query = "SELECT * FROM clientes WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente) {
            set_flash_message('danger', 'Cliente não encontrado.');
            redirect('/admin/clientes');
        }
        
        require 'views/admin/clientes/edit.php';
    }
    
    public function update() {
        if (!is_post_request()) {
            redirect('/admin/clientes');
        }
        
        // Verificar CSRF token
        if (!verify_csrf_token($_POST['csrf_token'])) {
            set_flash_message('danger', 'Erro de validação. Por favor, tente novamente.');
            redirect('/admin/clientes');
        }
        
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($id <= 0) {
            set_flash_message('danger', 'ID de cliente inválido.');
            redirect('/admin/clientes');
        }
        
        // Sanitizar e validar dados
        $nome = sanitize($_POST['nome'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $telefone = sanitize($_POST['telefone'] ?? '');
        $endereco = sanitize($_POST['endereco'] ?? '');
        $cidade = sanitize($_POST['cidade'] ?? '');
        $estado = sanitize($_POST['estado'] ?? '');
        $cep = sanitize($_POST['cep'] ?? '');
        $tipo = sanitize($_POST['tipo'] ?? 'residencial');
        $observacoes = sanitize($_POST['observacoes'] ?? '');
        
        // Validação básica
        if (empty($nome) || empty($email) || empty($telefone)) {
            set_flash_message('danger', 'Por favor, preencha todos os campos obrigatórios.');
            redirect('/admin/clientes/editar?id=' . $id);
        }
        
        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash_message('danger', 'Por favor, informe um email válido.');
            redirect('/admin/clientes/editar?id=' . $id);
        }
        
        try {
            // Verificar se o email já existe para outro cliente
            $query = "SELECT COUNT(*) as total FROM clientes WHERE email = :email AND id != :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                set_flash_message('danger', 'Este email já está cadastrado para outro cliente.');
                redirect('/admin/clientes/editar?id=' . $id);
            }
            
            // Atualizar cliente
            $query = "UPDATE clientes 
                      SET nome = :nome, email = :email, telefone = :telefone, 
                      endereco = :endereco, cidade = :cidade, estado = :estado, 
                      cep = :cep, tipo = :tipo, observacoes = :observacoes, 
                      data_atualizacao = NOW() 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':endereco', $endereco);
            $stmt->bindParam(':cidade', $cidade);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':cep', $cep);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':observacoes', $observacoes);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Cliente atualizado com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao atualizar cliente. Por favor, tente novamente.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação. Por favor, tente novamente.');
            
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/admin/clientes');
    }
    
    public function delete() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            set_flash_message('danger', 'ID de cliente inválido.');
            redirect('/admin/clientes');
        }
        
        try {
            // Verificar se o cliente está associado a algum agendamento
            $query = "SELECT COUNT(*) as total FROM agendamentos WHERE cliente_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                set_flash_message('danger', 'Este cliente não pode ser excluído pois está associado a agendamentos.');
                redirect('/admin/clientes');
            }
            
            // Excluir cliente
            $query = "DELETE FROM clientes WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Cliente excluído com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao excluir cliente. Por favor, tente novamente.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação. Por favor, tente novamente.');
            
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/admin/clientes');
    }
    
    public function agendamentos() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            set_flash_message('danger', 'ID de cliente inválido.');
            redirect('/admin/clientes');
        }
        
        // Buscar cliente pelo ID
        $query = "SELECT * FROM clientes WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente) {
            set_flash_message('danger', 'Cliente não encontrado.');
            redirect('/admin/clientes');
        }
        
        // Buscar agendamentos do cliente
        $query = "SELECT a.*, s.titulo as servico_nome, t.nome as tecnico_nome
                  FROM agendamentos a
                  LEFT JOIN servicos s ON a.servico_id = s.id
                  LEFT JOIN tecnicos t ON a.tecnico_id = t.id
                  WHERE a.cliente_id = :cliente_id
                  ORDER BY a.data_agendamento DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cliente_id', $id);
        $stmt->execute();
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require 'views/admin/clientes/agendamentos.php';
    }
}
