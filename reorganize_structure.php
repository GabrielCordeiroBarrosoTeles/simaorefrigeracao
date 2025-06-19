<?php
/**
 * Script para reorganizar a estrutura de arquivos do sistema
 * 
 * Este script deve ser executado na raiz do projeto para reorganizar
 * os arquivos soltos em uma estrutura de diretórios mais organizada.
 */

// Definir diretórios que serão criados (se não existirem)
$directories = [
    'admin', // Para arquivos administrativos
    'controllers/Admin', // Já existe, mas garantir
    'models', // Para modelos de dados
    'public', // Para arquivos públicos
    'public/js',
    'public/css',
    'public/img',
    'config', // Já existe, mas garantir
    'includes', // Já existe, mas garantir
    'views/admin/includes', // Já existe, mas garantir
];

// Criar diretórios
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
        echo "Diretório criado: $dir<br>";
    }
}

// Mapeamento de arquivos para mover
$file_mapping = [
    // Arquivos admin-* para pasta admin/
    'admin-dashboard.php' => 'admin/dashboard.php',
    'admin-login.php' => 'admin/login.php',
    'admin-logout.php' => 'admin/logout.php',
    'admin-form.php' => 'admin/form.php',
    'admin-save.php' => 'admin/save.php',
    'admin-delete.php' => 'admin/delete.php',
    'admin-table.php' => 'admin/table.php',
    'admin-calendario.php' => 'admin/calendario.php',
    'admin-calendario-json.php' => 'admin/calendario-json.php',
    'admin-settings.php' => 'admin/settings.php',
    'admin-profile.php' => 'admin/profile.php',
    'admin-depoimentos.php' => 'admin/depoimentos.php',
    'admin-estatisticas.php' => 'admin/estatisticas.php',
    'admin-contatos.php' => 'admin/contatos.php',
    'admin-record.php' => 'admin/record.php',
    'admin-security.php' => 'admin/security.php',
    
    // Arquivos JS e CSS para pasta public/
    'assets/js/main.js' => 'public/js/main.js',
    'assets/js/admin.js' => 'public/js/admin.js',
    'assets/css/style.css' => 'public/css/style.css',
    'assets/css/admin.css' => 'public/css/admin.css',
    
    // Arquivos de processamento para controllers/
    'processar-contato.php' => 'controllers/processar-contato.php',
    
    // Arquivos de debug e setup para pasta tools/
    'debug.php' => 'tools/debug.php',
    'debug_db.php' => 'tools/debug_db.php',
    'setup_database.php' => 'tools/setup_database.php',
    'fix_database.php' => 'tools/fix_database.php',
    'fix_tables.php' => 'tools/fix_tables.php',
    'fix_logout.php' => 'tools/fix_logout.php',
    'fix_all_issues.php' => 'tools/fix_all_issues.php',
    'fix_database_name.php' => 'tools/fix_database_name.php',
    'debug_estatisticas.php' => 'tools/debug_estatisticas.php',
    
    // Arquivos de teste para pasta tests/
    'test_login.php' => 'tests/test_login.php',
    'test_logout.php' => 'tests/test_logout.php',
    'test_logout_button.php' => 'tests/test_logout_button.php',
];

// Função para atualizar referências em arquivos PHP
function update_file_references($file_path, $file_mapping) {
    if (!file_exists($file_path)) return;
    
    $content = file_get_contents($file_path);
    
    // Substituir referências de arquivos
    foreach ($file_mapping as $old => $new) {
        // Substituir includes e requires
        $content = str_replace("include '$old'", "include '$new'", $content);
        $content = str_replace("include_once '$old'", "include_once '$new'", $content);
        $content = str_replace("require '$old'", "require '$new'", $content);
        $content = str_replace("require_once '$old'", "require_once '$new'", $content);
        
        // Substituir redirecionamentos
        $content = str_replace("header('Location: $old", "header('Location: $new", $content);
        $content = str_replace("redirect('$old", "redirect('$new", $content);
        
        // Substituir links
        $content = str_replace("href='$old'", "href='$new'", $content);
        $content = str_replace("action='$old'", "action='$new'", $content);
    }
    
    file_put_contents($file_path, $content);
}

// Mover arquivos e atualizar referências
echo "<h2>Plano de Reorganização de Arquivos</h2>";
echo "<p>Este é um plano para reorganizar os arquivos do sistema. Você deve revisar este plano antes de executá-lo.</p>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Arquivo Original</th><th>Nova Localização</th></tr>";

foreach ($file_mapping as $old => $new) {
    echo "<tr><td>$old</td><td>$new</td></tr>";
}

echo "</table>";

echo "<h3>Instruções para Implementação</h3>";
echo "<ol>";
echo "<li>Faça um backup completo do sistema antes de iniciar a reorganização</li>";
echo "<li>Mova cada arquivo para sua nova localização conforme a tabela acima</li>";
echo "<li>Atualize todas as referências nos arquivos PHP (includes, requires, redirecionamentos)</li>";
echo "<li>Atualize referências em arquivos HTML/CSS (links, scripts, imagens)</li>";
echo "<li>Teste o sistema após cada conjunto de alterações</li>";
echo "</ol>";

echo "<h3>Benefícios da Nova Estrutura</h3>";
echo "<ul>";
echo "<li>Melhor organização e separação de responsabilidades</li>";
echo "<li>Facilidade de manutenção e expansão</li>";
echo "<li>Maior segurança com arquivos sensíveis fora da pasta pública</li>";
echo "<li>Código mais limpo e profissional</li>";
echo "</ul>";
?>
