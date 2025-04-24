<?php
// Iniciar sessão
session_start();

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitizar dados do formulário
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
    $servico = filter_input(INPUT_POST, 'servico', FILTER_SANITIZE_STRING);
    $mensagem = filter_input(INPUT_POST, 'mensagem', FILTER_SANITIZE_STRING);
    
    // Validação básica
    $errors = [];
    
    if (empty($nome)) {
        $errors[] = "O nome é obrigatório.";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido.";
    }
    
    if (empty($telefone)) {
        $errors[] = "O telefone é obrigatório.";
    }
    
    if (empty($mensagem)) {
        $errors[] = "A mensagem é obrigatória.";
    }
    
    // Se houver erros, redirecionar de volta com mensagem
    if (!empty($errors)) {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => implode('<br>', $errors)
        ];
        
        // Salvar dados do formulário para preencher novamente
        $_SESSION['form_data'] = [
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'servico' => $servico,
            'mensagem' => $mensagem
        ];
        
        header("Location: index.php#contato");
        exit;
    }
    
    // Configurações para envio de email
    $para = "simaorefrigeracao2@gmail.com"; // Email da empresa
    $assunto = "Novo contato do site - Simão Refrigeração";
    
    // Montar corpo do email
    $corpo_email = "Nome: $nome\n";
    $corpo_email .= "Email: $email\n";
    $corpo_email .= "Telefone: $telefone\n";
    $corpo_email .= "Serviço: $servico\n";
    $corpo_email .= "Mensagem: $mensagem\n";
    
    // Cabeçalhos do email
    $headers = "From: $nome <$email>\r\n";
    $headers .= "Reply-To: $email\r\n";
    
    // Tentar enviar o email
    if (mail($para, $assunto, $corpo_email, $headers)) {
        // Email enviado com sucesso
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Mensagem enviada com sucesso! Entraremos em contato em breve.'
        ];
        
        // Opcional: Salvar no banco de dados
        // Aqui você adicionaria o código para salvar no banco de dados
        
    } else {
        // Falha ao enviar email
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'Erro ao enviar mensagem. Por favor, tente novamente ou entre em contato por telefone.'
        ];
    }
    
    // Redirecionar de volta para a página de contato
    header("Location: index.php#contato");
    exit;
} else {
    // Se alguém tentar acessar este arquivo diretamente, redirecionar para a página inicial
    header("Location: index.php");
    exit;
}
?>
