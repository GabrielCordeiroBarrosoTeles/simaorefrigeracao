<?php
// Arquivo para testar o logout
session_start();

// Definir uma variável de sessão para teste
$_SESSION['test'] = 'teste';

echo "<h2>Teste de Logout</h2>";
echo "<p>Variável de sessão definida: " . $_SESSION['test'] . "</p>";
echo "<p>ID da sessão: " . session_id() . "</p>";
echo "<p><a href='logout.php'>Fazer logout</a></p>";
?>
