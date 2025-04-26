<?php
class TecnicoController {
    private $db;
    
    public function __construct() {
        // Verificar se o usuário está logado
        if (!is_logged_in()) {
            redirect('/admin/login');
        }
        
        // Verificar se o usuário é um técnico
        if (!is_tecnico()) {
            set_flash_message('danger', 'Você não tem permissão para acessar esta área.');
            redirect('/admin');
        }
        
        $this->db = db_connect();
    }
    
    public function index() {
        // Obter o ID do técnico associado ao usuário logado
        $tecnico_id = get_tecnico_id();
        
        if (!$tecnico_id) {
            set_flash_message('danger', 'Seu usuário não está associado a nenhum técnico.');
            redirect('/admin');
        }
        
        // Buscar informações do técnico
        $query = "SELECT * FROM tecnicos WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $tecnico_id);
        $stmt->execute();
        $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Buscar agendamentos do técnico
        $query = "SELECT a.*, c.nome as cliente_nome, c.telefone as cliente_telefone, 
                  c.endereco as cliente_endereco, c.cidade as cliente_cidade, 
                  c.estado as cliente_estado, s.titulo as servico_nome
                  FROM agendamentos a
                  LEFT JOIN clientes c ON a.cliente_id = c.id
                  LEFT JOIN servicos s ON a.servico_id = s.id
                  WHERE a.tecnico_id = :tecnico_id
                  ORDER BY a.data_agendamento ASC, a.hora_inicio ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tecnico_id', $tecnico_id);
        $stmt->execute();
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Filtrar agendamentos para hoje
        $hoje = date('Y-m-d');
        $agendamentos_hoje = array_filter($agendamentos, function($a) use ($hoje) {
            return $a['data_agendamento'] == $hoje;
        });
        
        // Filtrar agendamentos pendentes
        $agendamentos_pendentes = array_filter($agendamentos, function($a) {
            return $a['status'] == 'pendente';
        });
        
        require 'views/tecnico/dashboard.php';
    }
    
    public function calendario() {
        // Obter o ID do técnico associado ao usuário logado
        $tecnico_id = get_tecnico_id();
        
        if (!$tecnico_id) {
            set_flash_message('danger', 'Seu usuário não está associado a nenhum técnico.');
            redirect('/admin');
        }
        
        // Buscar informações do técnico
        $query = "SELECT * FROM tecnicos WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $tecnico_id);
        $stmt->execute();
        $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
        
        require 'views/tecnico/calendario.php';
    }
    
    public function agendamento() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            set_flash_message('danger', 'ID de agendamento inválido.');
            redirect('/tecnico');
        }
        
        // Obter o ID do técnico associado ao usuário logado
        $tecnico_id = get_tecnico_id();
        
        if (!$tecnico_id) {
            set_flash_message('danger', 'Seu usuário não está associado a nenhum técnico.');
            redirect('/admin');
        }
        
        // Buscar agendamento pelo ID e verificar se pertence ao técnico
        $query = "SELECT a.*, c.nome as cliente_nome, c.telefone as cliente_telefone, 
                  c.email as cliente_email, c.endereco as cliente_endereco, 
                  c.cidade as cliente_cidade, c.estado as cliente_estado, 
                  c.tipo as cliente_tipo, s.titulo as servico_nome
                  FROM agendamentos a
                  LEFT JOIN clientes c ON a.cliente_id = c.id
                  LEFT JOIN servicos s ON a.servico_id = s.id
                  WHERE a.id = :id AND a.tecnico_id = :tecnico_id
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':tecnico_id', $tecnico_id);
        $stmt->execute();
        
        $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$agendamento) {
            set_flash_message('danger', 'Agendamento não encontrado ou não pertence a você.');
            redirect('/tecnico');
        }
        
        require 'views/tecnico/agendamento.php';
    }
    
    public function atualizarStatus() {
        if (!is_post_request()) {
            redirect('/tecnico');
        }
        
        // Verificar CSRF token
        if (!verify_csrf_token($_POST['csrf_token'])) {
            set_flash_message('danger', 'Erro de validação. Por favor, tente novamente.');
            redirect('/tecnico');
        }
        
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $status = sanitize($_POST['status'] ?? '');
        $observacoes = sanitize($_POST['observacoes'] ?? '');
        
        if ($id <= 0 || empty($status)) {
            set_flash_message('danger', 'Parâmetros inválidos.');
            redirect('/tecnico');
        }
        
        // Obter o ID do técnico associado ao usuário logado
        $tecnico_id = get_tecnico_id();
        
        if (!$tecnico_id) {
            set_flash_message('danger', 'Seu usuário não está associado a nenhum técnico.');
            redirect('/admin');
        }
        
        try {
            // Verificar se o agendamento pertence ao técnico
            $query = "SELECT id FROM agendamentos WHERE id = :id AND tecnico_id = :tecnico_id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':tecnico_id', $tecnico_id);
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                set_flash_message('danger', 'Agendamento não encontrado ou não pertence a você.');
                redirect('/tecnico');
            }
            
            // Atualizar status do agendamento
            $query = "UPDATE agendamentos 
                      SET status = :status, observacoes = :observacoes, data_atualizacao = NOW() 
                      WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':observacoes', $observacoes);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Status do agendamento atualizado com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao atualizar status do agendamento.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação.');
            
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/tecnico/agendamento?id=' . $id);
    }
    
    // API para o calendário do técnico
    public function api() {
        header('Content-Type: application/json');
        
        if (!is_logged_in() || !is_tecnico()) {
            echo json_encode(['error' => 'Não autorizado']);
            exit;
        }
        
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        
        switch ($action) {
            case 'agendamentos':
                $this->apiGetAgendamentos();
                break;
            default:
                echo json_encode(['error' => 'Ação inválida']);
                break;
        }
    }
    
    private function apiGetAgendamentos() {
        // Obter o ID do técnico associado ao usuário logado
        $tecnico_id = get_tecnico_id();
        
        if (!$tecnico_id) {
            echo json_encode(['error' => 'Seu usuário não está associado a nenhum técnico.']);
            exit;
        }
        
        $start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d');
        $end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d', strtotime('+30 days'));
        
        try {
            // Buscar agendamentos do técnico no período especificado
            $query = "SELECT a.id, a.titulo, a.data_agendamento, a.hora_inicio, a.hora_fim, 
                      a.observacoes, a.status, c.nome as cliente_nome
                      FROM agendamentos a
                      LEFT JOIN clientes c ON a.cliente_id = c.id
                      WHERE a.tecnico_id = :tecnico_id 
                      AND a.data_agendamento BETWEEN :start AND :end
                      ORDER BY a.data_agendamento ASC, a.hora_inicio ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':tecnico_id', $tecnico_id);
            $stmt->bindParam(':start', $start);
            $stmt->bindParam(':end', $end);
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
                
                $eventos[] = [
                    'id' => $agendamento['id'],
                    'title' => $agendamento['titulo'] . ' - ' . $agendamento['cliente_nome'],
                    'start' => $data_inicio,
                    'end' => $data_fim,
                    'color' => $cor,
                    'url' => '/tecnico/agendamento?id=' . $agendamento['id'],
                    'extendedProps' => [
                        'cliente' => $agendamento['cliente_nome'],
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
}
