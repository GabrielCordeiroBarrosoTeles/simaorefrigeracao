<?php
class ContatoController {
    private $db;
    
    public function __construct() {
        $this->db = db_connect();
    }
    
    public function index() {
        // Buscar serviços para o dropdown
        $query = "SELECT id, titulo FROM servicos ORDER BY titulo ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar informações de contato
        $query = "SELECT * FROM configuracoes WHERE id = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $configuracoes = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Carregar a view
        require 'views/contato.php';
    }
    
    public function enviar() {
        if (!is_post_request()) {
            redirect('/contato');
        }
        
        // Verificar CSRF token
        if (!verify_csrf_token($_POST['csrf_token'])) {
            set_flash_message('danger', 'Erro de validação. Por favor, tente novamente.');
            redirect('/contato');
        }
        
        // Sanitizar e validar dados
        $nome = sanitize($_POST['nome'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $telefone = sanitize($_POST['telefone'] ?? '');
        $servico_id = sanitize($_POST['servico'] ?? '');
        $mensagem = sanitize($_POST['mensagem'] ?? '');
        
        // Validação básica
        $errors = [];
        
        if (empty($nome)) {
            $errors[] = 'O nome é obrigatório.';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido.';
        }
        
        if (empty($telefone)) {
            $errors[] = 'O telefone é obrigatório.';
        }
        
        if (empty($mensagem)) {
            $errors[] = 'A mensagem é obrigatória.';
        }
        
        // Se houver erros, redirecionar de volta com mensagem
        if (!empty($errors)) {
            $_SESSION['form_data'] = [
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone,
                'servico' => $servico_id,
                'mensagem' => $mensagem,
                'errors' => $errors
            ];
            
            redirect('/contato');
        }
        
        // Inserir no banco de dados
        try {
            $query = "INSERT INTO contatos (nome, email, telefone, servico_id, mensagem, data_criacao) 
                      VALUES (:nome, :email, :telefone, :servico_id, :mensagem, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':servico_id', $servico_id);
            $stmt->bindParam(':mensagem', $mensagem);
            
            if ($stmt->execute()) {
                // Enviar email de notificação (implementação básica)
                $to = EMAIL_FROM;
                $subject = "Novo contato do site - " . SITE_NAME;
                $message = "Nome: $nome\n";
                $message .= "Email: $email\n";
                $message .= "Telefone: $telefone\n";
                $message .= "Serviço: " . ($servico_id ? $this->getServicoNome($servico_id) : 'Não especificado') . "\n";
                $message .= "Mensagem: $mensagem\n";
                
                $headers = "From: " . EMAIL_FROM . "\r\n";
                $headers .= "Reply-To: $email\r\n";
                
                mail($to, $subject, $message, $headers);
                
                // Enviar email de confirmação para o cliente
                $to_client = $email;
                $subject_client = "Recebemos seu contato - " . SITE_NAME;
                $message_client = "Olá $nome,\n\n";
                $message_client .= "Recebemos seu contato e retornaremos em breve.\n\n";
                $message_client .= "Atenciosamente,\n";
                $message_client .= SITE_NAME;
                
                $headers_client = "From: " . EMAIL_FROM . "\r\n";
                
                mail($to_client, $subject_client, $message_client, $headers_client);
                
                set_flash_message('success', 'Mensagem enviada com sucesso! Entraremos em contato em breve.');
            } else {
                set_flash_message('danger', 'Erro ao enviar mensagem. Por favor, tente novamente.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação. Por favor, tente novamente.');
            
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/contato');
    }
    
    private function getServicoNome($id) {
        $query = "SELECT titulo FROM servicos WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['titulo'] : 'Não encontrado';
    }
}
