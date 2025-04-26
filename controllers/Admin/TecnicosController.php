<?php
class TecnicosController {
    private $db;
    
    public function __construct() {
        // Verificar se o usuário está logado
        if (!is_logged_in()) {
            redirect('/admin/login');
        }
        
        // Verificar se o usuário tem permissão para acessar esta área
        if (!user_has_access(['admin', 'tecnico_adm'])) {
            set_flash_message('danger', 'Você não tem permissão para acessar esta área.');
            redirect('/admin');
        }
        
        $this->db = db_connect();
    }
    
    public function index() {
        try {
            // Verificar conexão com o banco
            if (!$this->db) {
                error_log("Erro: Conexão com o banco de dados falhou em TecnicosController::index()");
                set_flash_message('danger', 'Erro de conexão com o banco de dados.');
                require 'views/admin/tecnicos/index.php';
                return;
            }
            
            // Buscar todos os técnicos
            $query = "SELECT t.*, u.email as usuario_email, u.nivel as usuario_nivel 
                      FROM tecnicos t
                      LEFT JOIN usuarios u ON t.usuario_id = u.id
                      ORDER BY t.nome ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Verificar se há técnicos
            if (empty($tecnicos)) {
                error_log("Aviso: Nenhum técnico encontrado em TecnicosController::index()");
            }
            
            require 'views/admin/tecnicos/index.php';
        } catch (PDOException $e) {
            error_log("Erro PDO em TecnicosController::index(): " . $e->getMessage());
            set_flash_message('danger', 'Erro ao buscar técnicos: ' . $e->getMessage());
            $tecnicos = [];
            require 'views/admin/tecnicos/index.php';
        } catch (Exception $e) {
            error_log("Erro geral em TecnicosController::index(): " . $e->getMessage());
            set_flash_message('danger', 'Erro inesperado: ' . $e->getMessage());
            $tecnicos = [];
            require 'views/admin/tecnicos/index.php';
        }
    }
    
    public function create() {
        // Buscar usuários disponíveis (que não estão associados a nenhum técnico)
        $query = "SELECT u.id, u.nome, u.email, u.nivel 
                  FROM usuarios u
                  LEFT JOIN tecnicos t ON u.id = t.usuario_id
                  WHERE t.id IS NULL AND (u.nivel = 'tecnico' OR u.nivel = 'tecnico_adm')
                  ORDER BY u.nome ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $usuarios_disponiveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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
        $usuario_id = isset($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : null;
        $criar_usuario = isset($_POST['criar_usuario']) ? (bool)$_POST['criar_usuario'] : false;
        $nivel_usuario = sanitize($_POST['nivel_usuario'] ?? 'tecnico');
        $senha = $_POST['senha'] ?? '';
        
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
            // Iniciar transação
            $this->db->beginTransaction();
            
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
            
            // Se for para criar um novo usuário
            if ($criar_usuario && empty($usuario_id)) {
                if (empty($senha)) {
                    set_flash_message('danger', 'Por favor, informe uma senha para o novo usuário.');
                    redirect('/admin/tecnicos/novo');
                }
                
                // Verificar se o email já existe na tabela de usuários
                $query = "SELECT COUNT(*) as total FROM usuarios WHERE email = :email";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result['total'] > 0) {
                    set_flash_message('danger', 'Este email já está cadastrado para outro usuário.');
                    redirect('/admin/tecnicos/novo');
                }
                
                // Criar novo usuário
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $query = "INSERT INTO usuarios (nome, email, senha, nivel, data_criacao) 
                          VALUES (:nome, :email, :senha, :nivel, NOW())";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':senha', $senha_hash);
                $stmt->bindParam(':nivel', $nivel_usuario);
                $stmt->execute();
                
                $usuario_id = $this->db->lastInsertId();
            }
            
            // Inserir novo técnico
            $query = "INSERT INTO tecnicos (nome, email, telefone, especialidade, cor, status, usuario_id, data_criacao) 
                      VALUES (:nome, :email, :telefone, :especialidade, :cor, :status, :usuario_id, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':especialidade', $especialidade);
            $stmt->bindParam(':cor', $cor);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->execute();
            
            // Confirmar transação
            $this->db->commit();
            
            set_flash_message('success', 'Técnico adicionado com sucesso!');
        } catch (PDOException $e) {
            // Reverter transação em caso de erro
            $this->db->rollBack();
            
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
        $query = "SELECT t.*, u.id as usuario_id, u.nivel as usuario_nivel 
                  FROM tecnicos t
                  LEFT JOIN usuarios u ON t.usuario_id = u.id
                  WHERE t.id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tecnico) {
            set_flash_message('danger', 'Técnico não encontrado.');
            redirect('/admin/tecnicos');
        }
        
        // Buscar usuários disponíveis (que não estão associados a nenhum técnico ou estão associados a este técnico)
        $query = "SELECT u.id, u.nome, u.email, u.nivel 
                  FROM usuarios u
                  LEFT JOIN tecnicos t ON u.id = t.usuario_id AND t.id != :tecnico_id
                  WHERE (t.id IS NULL OR u.id = :usuario_id) AND (u.nivel = 'tecnico' OR u.nivel = 'tecnico_adm')
                  ORDER BY u.nome ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tecnico_id', $id);
        $stmt->bindParam(':usuario_id', $tecnico['usuario_id']);
        $stmt->execute();
        $usuarios_disponiveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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
        $usuario_id = isset($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : null;
        $criar_usuario = isset($_POST['criar_usuario']) ? (bool)$_POST['criar_usuario'] : false;
        $nivel_usuario = sanitize($_POST['nivel_usuario'] ?? 'tecnico');
        $senha = $_POST['senha'] ?? '';
        $usuario_atual_id = isset($_POST['usuario_atual_id']) ? (int)$_POST['usuario_atual_id'] : null;
        
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
            // Iniciar transação
            $this->db->beginTransaction();
            
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
            
            // Se for para criar um novo usuário
            if ($criar_usuario && empty($usuario_id)) {
                if (empty($senha)) {
                    set_flash_message('danger', 'Por favor, informe uma senha para o novo usuário.');
                    redirect('/admin/tecnicos/editar?id=' . $id);
                }
                
                // Verificar se o email já existe na tabela de usuários
                $query = "SELECT COUNT(*) as total FROM usuarios WHERE email = :email";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result['total'] > 0) {
                    set_flash_message('danger', 'Este email já está cadastrado para outro usuário.');
                    redirect('/admin/tecnicos/editar?id=' . $id);
                }
                
                // Criar novo usuário
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $query = "INSERT INTO usuarios (nome, email, senha, nivel, data_criacao) 
                          VALUES (:nome, :email, :senha, :nivel, NOW())";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':senha', $senha_hash);
                $stmt->bindParam(':nivel', $nivel_usuario);
                $stmt->execute();
                
                $usuario_id = $this->db->lastInsertId();
            } 
            // Se estiver alterando o nível do usuário existente
            elseif ($usuario_id && $usuario_id == $usuario_atual_id) {
                $query = "UPDATE usuarios SET nivel = :nivel WHERE id = :id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':nivel', $nivel_usuario);
                $stmt->bindParam(':id', $usuario_id);
                $stmt->execute();
            }
            
            // Atualizar técnico
            $query = "UPDATE tecnicos 
                      SET nome = :nome, email = :email, telefone = :telefone, 
                      especialidade = :especialidade, cor = :cor, status = :status, 
                      usuario_id = :usuario_id, data_atualizacao = NOW() 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':especialidade', $especialidade);
            $stmt->bindParam(':cor', $cor);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            // Confirmar transação
            $this->db->commit();
            
            set_flash_message('success', 'Técnico atualizado com sucesso!');
        } catch (PDOException $e) {
            // Reverter transação em caso de erro
            $this->db->rollBack();
            
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
            
            // Iniciar transação
            $this->db->beginTransaction();
            
            // Obter o ID do usuário associado ao técnico
            $query = "SELECT usuario_id FROM tecnicos WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Excluir técnico
            $query = "DELETE FROM tecnicos WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            // Confirmar transação
            $this->db->commit();
            
            set_flash_message('success', 'Técnico excluído com sucesso!');
        } catch (PDOException $e) {
            // Reverter transação em caso de erro
            $this->db->rollBack();
            
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
