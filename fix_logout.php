<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo "<h2>Você não está logado.</h2>";
    echo "<p><a href='admin-login.php' class='btn'>Ir para a página de login</a></p>";
    exit;
}

// Exibir informações da sessão atual
echo "<h2>Informações da sessão atual:</h2>";
echo "<ul>";
echo "<li><strong>ID do usuário:</strong> " . $_SESSION['user_id'] . "</li>";
echo "<li><strong>Nome:</strong> " . ($_SESSION['user_nome'] ?? 'Não definido') . "</li>";
echo "<li><strong>Email:</strong> " . ($_SESSION['user_email'] ?? 'Não definido') . "</li>";
echo "<li><strong>Nível:</strong> " . ($_SESSION['user_nivel'] ?? 'Não definido') . "</li>";
echo "</ul>";

// Botão para testar o logout
echo "<h3>Testar logout:</h3>";
echo "<form action='logout.php' method='post'>";
echo "<button type='submit' class='btn'>Fazer logout</button>";
echo "</form>";

// Criar arquivo de logout corrigido
$logout_file = 'logout.php';
$logout_content = '<?php
// Iniciar sessão
session_start();

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Se houver um cookie de sessão, destruí-lo também
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), "", time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir a sessão
session_destroy();

// Redirecionar para a página de login
header("Location: admin-login.php?logout=" . time());
exit;
';

file_put_contents($logout_file, $logout_content);
echo "<p>Arquivo de logout corrigido criado: {$logout_file}</p>";

// Verificar links de logout no admin-dashboard.php
$dashboard_file = 'admin-dashboard.php';
if (file_exists($dashboard_file)) {
    $dashboard_content = file_get_contents($dashboard_file);
    
    // Verificar se há links incorretos para logout
    if (strpos($dashboard_content, 'admin-dashboard.php?logout=1') !== false) {
        // Corrigir links de logout
        $dashboard_content = str_replace('admin-dashboard.php?logout=1', 'logout.php', $dashboard_content);
        file_put_contents($dashboard_file, $dashboard_content);
        echo "<p>Links de logout corrigidos no arquivo {$dashboard_file}</p>";
    } else {
        echo "<p>Não foram encontrados links de logout incorretos no arquivo {$dashboard_file}</p>";
    }
} else {
    echo "<p>Arquivo {$dashboard_file} não encontrado</p>";
}

echo "<h3>Correções concluídas!</h3>";
echo "<p><a href='admin-dashboard.php' class='btn'>Voltar para o Dashboard</a></p>";
?>

<style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        margin: 20px;
        padding: 20px;
        max-width: 800px;
        margin: 0 auto;
    }
    h2, h3 {
        color: #2563eb;
    }
    ul {
        margin-bottom: 20px;
    }
    li {
        margin-bottom: 5px;
    }
    p {
        margin-bottom: 10px;
    }
    .btn {
        display: inline-block;
        background-color: #2563eb;
        color: white;
        padding: 10px 15px;
        text-decoration: none;
        border-radius: 4px;
        margin-top: 20px;
        border: none;
        cursor: pointer;
    }
    .btn:hover {
        background-color: #1d4ed8;
    }
</style>
