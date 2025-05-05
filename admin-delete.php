<?php
// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/functions.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
    redirect('/admin-login.php');
}

// Obter parâmetros
$table = isset($_GET['table']) ? sanitize($_GET['table']) : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verificar se a tabela é válida
$valid_tables = ['clientes', 'tecnicos', 'servicos', 'agendamentos', 'depoimentos', 'contatos', 'usuarios', 'estatisticas'];
if (!in_array($table, $valid_tables)) {
    set_flash_message('danger', 'Tabela inválida.');
    redirect('/admin-dashboard.php');
}

// Verificar se o ID é válido
if ($id <= 0) {
    set_flash_message('danger', 'ID inválido.');
    redirect('/admin-table.php?table=' . $table);
}

// Verificar se o usuário está tentando excluir a si mesmo
if ($table === 'usuarios' && $id === $_SESSION['user_id']) {
    set_flash_message('danger', 'Você não pode excluir seu próprio usuário.');
    redirect('/admin-table.php?table=' . $table);
}

// Conectar ao banco de dados
$db = db_connect();

// Verificar se é uma configuração (não pode ser excluída)
if ($table === 'configuracoes') {
    set_flash_message('danger', 'Configurações não podem ser excluídas.');
    redirect('/admin-table.php?table=' . $table);
}

// Excluir registro
try {
    // Verificar se há arquivos para excluir
    if ($table === 'depoimentos') {
        $stmt = $db->prepare("SELECT foto FROM depoimentos WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $foto = $stmt->fetchColumn();
        
        if ($foto && file_exists($foto)) {
            unlink($foto);
        }
    }
    
    $sql = "DELETE FROM {$table} WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        set_flash_message('success', 'Registro excluído com sucesso!');
    } else {
        set_flash_message('warning', 'Nenhum registro foi excluído.');
    }
} catch (PDOException $e) {
    set_flash_message('danger', 'Erro ao excluir registro: ' . $e->getMessage());
}

// Redirecionar de volta para a tabela
redirect('/admin-table.php?table=' . $table);
