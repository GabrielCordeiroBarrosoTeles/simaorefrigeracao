<?php
class ServicosController {
    private $db;
    
    public function __construct() {
        // Verificar se o usuário está logado
        if (!is_logged_in()) {
            redirect('/admin/login');
        }
        
        $this->db = db_connect();
    }
    
    public function index() {
        // Buscar todos os serviços
        $query = "SELECT * FROM servicos ORDER BY id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require 'views/admin/servicos/index.php';
    }
    
    public function create() {
        require 'views/admin/servicos/create.php';
    }
    
    public function store() {
        if (!is_post_request()) {
            redirect('/admin/servicos');
        }
        
        // Verificar CSRF token
        if (!verify_csrf_token($_POST['csrf_token'])) {
            set_flash_message('danger', 'Erro de validação. Por favor, tente novamente.');
            redirect('/admin/servicos/novo');
        }
        
        // Sanitizar e validar dados
        $titulo = sanitize($_POST['titulo'] ?? '');
        $icone = sanitize($_POST['icone'] ?? '');
        $descricao = sanitize($_POST['descricao'] ?? '');
        $itens = isset($_POST['itens']) ? explode("\n", $_POST['itens']) : [];
        
        // Sanitizar cada item
        foreach ($itens as $key => $item) {
            $itens[$key] = sanitize(trim($item));
        }
        
        // Remover itens vazios
        $itens = array_filter($itens, function($item) {
            return !empty($item);
        });
        
        // Converter array de itens para JSON
        $itens_json = json_encode($itens);
        
        // Validação básica
        if (empty($titulo) || empty($descricao) || empty($itens)) {
            set_flash_message('danger', 'Por favor, preencha todos os campos obrigatórios.');
            redirect('/admin/servicos/novo');
        }
        
        try {
            $query = "INSERT INTO servicos (titulo, icone, descricao, itens, data_criacao) 
                      VALUES (:titulo, :icone, :descricao, :itens, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':icone', $icone);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':itens', $itens_json);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Serviço adicionado com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao adicionar serviço. Por favor, tente novamente.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação. Por favor, tente novamente.');
            
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/admin/servicos');
    }
    
    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            set_flash_message('danger', 'ID de serviço inválido.');
            redirect('/admin/servicos');
        }
        
        // Buscar serviço pelo ID
        $query = "SELECT * FROM servicos WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $servico = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$servico) {
            set_flash_message('danger', 'Serviço não encontrado.');
            redirect('/admin/servicos');
        }
        
        // Decodificar itens JSON para array
        $servico['itens_array'] = json_decode($servico['itens'], true);
        
        require 'views/admin/servicos/edit.php';
    }
    
    public function update() {
        if (!is_post_request()) {
            redirect('/admin/servicos');
        }
        
        // Verificar CSRF token
        if (!verify_csrf_token($_POST['csrf_token'])) {
            set_flash_message('danger', 'Erro de validação. Por favor, tente novamente.');
            redirect('/admin/servicos');
        }
        
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($id <= 0) {
            set_flash_message('danger', 'ID de serviço inválido.');
            redirect('/admin/servicos');
        }
        
        // Sanitizar e validar dados
        $titulo = sanitize($_POST['titulo'] ?? '');
        $icone = sanitize($_POST['icone'] ?? '');
        $descricao = sanitize($_POST['descricao'] ?? '');
        $itens = isset($_POST['itens']) ? explode("\n", $_POST['itens']) : [];
        
        // Sanitizar cada item
        foreach ($itens as $key => $item) {
            $itens[$key] = sanitize(trim($item));
        }
        
        // Remover itens vazios
        $itens = array_filter($itens, function($item) {
            return !empty($item);
        });
        
        // Converter array de itens para JSON
        $itens_json = json_encode($itens);
        
        // Validação básica
        if (empty($titulo) || empty($descricao) || empty($itens)) {
            set_flash_message('danger', 'Por favor, preencha todos os campos obrigatórios.');
            redirect('/admin/servicos/editar?id=' . $id);
        }
        
        try {
            $query = "UPDATE servicos 
                      SET titulo = :titulo, icone = :icone, descricao = :descricao, itens = :itens, data_atualizacao = NOW() 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':icone', $icone);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':itens', $itens_json);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Serviço atualizado com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao atualizar serviço. Por favor, tente novamente.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação. Por favor, tente novamente.');
            
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/admin/servicos');
    }
    
    public function delete() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            set_flash_message('danger', 'ID de serviço inválido.');
            redirect('/admin/servicos');
        }
        
        try {
            $query = "DELETE FROM servicos WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Serviço excluído com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao excluir serviço. Por favor, tente novamente.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação. Por favor, tente novamente.');
            
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/admin/servicos');
    }
}
