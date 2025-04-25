<?php
// Iniciar sessão
session_start();

// Carregar configurações e funções
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/functions.php';

// Testar conexão com o banco de dados
echo "<h2>Testando conexão com o banco de dados</h2>";
try {
    $db = db_connect();
    if ($db) {
        echo "<p style='color:green'>Conexão com o banco de dados estabelecida com sucesso!</p>";
        
        // Verificar se a tabela de usuários existe
        $query = "SHOW TABLES LIKE 'usuarios'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo "<p style='color:green'>Tabela 'usuarios' encontrada.</p>";
            
            // Verificar se existe algum usuário
            $query = "SELECT * FROM usuarios";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($usuarios) > 0) {
                echo "<p style='color:green'>Encontrados " . count($usuarios) . " usuários no banco de dados:</p>";
                echo "<ul>";
                foreach ($usuarios as $usuario) {
                    echo "<li>ID: " . $usuario['id'] . " | Nome: " . $usuario['nome'] . " | Email: " . $usuario['email'] . " | Nível: " . $usuario['nivel'] . "</li>";
                }
                echo "</ul>";
                
                // Verificar se o usuário admin@friocerto.com.br existe
                $query = "SELECT * FROM usuarios WHERE email = 'admin@friocerto.com.br'";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($admin) {
                    echo "<p style='color:green'>Usuário administrador encontrado com email admin@friocerto.com.br.</p>";
                    echo "<p>Senha para login: admin123</p>";
                } else {
                    echo "<p style='color:red'>Usuário administrador com email admin@friocerto.com.br não encontrado.</p>";
                    echo "<p>Execute o script SQL 'atualizar_usuario_admin.sql' para criar o usuário administrador.</p>";
                }
            } else {
                echo "<p style='color:red'>Nenhum usuário encontrado na tabela 'usuarios'.</p>";
            }
        } else {
            echo "<p style='color:red'>Tabela 'usuarios' não encontrada.</p>";
        }
    } else {
        echo "<p style='color:red'>Falha ao conectar com o banco de dados.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro ao conectar com o banco de dados: " . $e->getMessage() . "</p>";
}

// Exibir informações de sessão
echo "<h2>Informações de sessão</h2>";
if (isset($_SESSION) && !empty($_SESSION)) {
    echo "<p>Variáveis de sessão:</p>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
    if (is_logged_in()) {
        echo "<p style='color:green'>Usuário está logado.</p>";
    } else {
        echo "<p style='color:orange'>Usuário não está logado.</p>";
    }
} else {
    echo "<p>Nenhuma variável de sessão definida.</p>";
}

// Exibir link para a página de login
echo "<p><a href='/admin/login' style='display: inline-block; padding: 10px 20px; background-color: #2563eb; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;'>Ir para a página de login</a></p>";
?>
