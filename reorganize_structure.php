<?php
/**
 * Script para reorganizar a estrutura de arquivos do projeto
 * Move arquivos da raiz para diretórios apropriados
 */

// Definir diretórios de destino
$directories = [
    'public/admin' => [],
    'public/tecnico' => [],
    'public/api' => [],
    'public/assets/js' => [],
    'public/assets/css' => [],
    'public/assets/img' => [],
    'scripts' => [],
    'config' => [],
];

// Criar diretórios se não existirem
foreach (array_keys($directories) as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "Diretório criado: $dir\n";
    }
}

// Mapeamento de arquivos para mover (origem => destino)
$filesToMove = [
    // Arquivos admin
    'admin-adicionar-agendamentos.php' => 'public/admin/adicionar-agendamentos.php',
    'admin-calendario-json.php' => 'public/admin/calendario-json.php',
    'admin-calendario.php' => 'public/admin/calendario.php',
    'admin-contatos.php' => 'public/admin/contatos.php',
    'admin-dashboard.php' => 'public/admin/dashboard.php',
    'admin-delete.php' => 'public/admin/delete.php',
    'admin-depoimentos.php' => 'public/admin/depoimentos.php',
    'admin-estatisticas.php' => 'public/admin/estatisticas.php',
    'admin-form.php' => 'public/admin/form.php',
    'admin-login.php' => 'public/admin/login.php',
    'admin-profile.php' => 'public/admin/profile.php',
    'admin-record.php' => 'public/admin/record.php',
    'admin-save.php' => 'public/admin/save.php',
    'admin-security.php' => 'public/admin/security.php',
    'admin-settings.php' => 'public/admin/settings.php',
    'admin-table.php' => 'public/admin/table.php',
    
    // Arquivos técnico
    'tecnico-agendamento.php' => 'public/tecnico/agendamento.php',
    'tecnico-agendamentos.php' => 'public/tecnico/agendamentos.php',
    'tecnico-api.php' => 'public/tecnico/api.php',
    'tecnico-atualizar-status.php' => 'public/tecnico/atualizar-status.php',
    'tecnico-calendario.php' => 'public/tecnico/calendario.php',
    'tecnico-dashboard.php' => 'public/tecnico/dashboard.php',
    'tecnico-profile.php' => 'public/tecnico/profile.php',
    
    // Arquivos API
    'api.php' => 'public/api/index.php',
    'exportar-xml.php' => 'public/api/exportar-xml.php',
    'get-garantia.php' => 'public/api/get-garantia.php',
    
    // Scripts de utilidade
    'debug.php' => 'scripts/debug.php',
    'debug_db.php' => 'scripts/debug_db.php',
    'debug_estatisticas.php' => 'scripts/debug_estatisticas.php',
    'fix_all_issues.php' => 'scripts/fix_all_issues.php',
    'fix_database.php' => 'scripts/fix_database.php',
    'fix_database_name.php' => 'scripts/fix_database_name.php',
    'fix_logout.php' => 'scripts/fix_logout.php',
    'fix_tables.php' => 'scripts/fix_tables.php',
    'organize_files.php' => 'scripts/organize_files.php',
    'seed-db.php' => 'scripts/seed-db.php',
    'setup-db.php' => 'scripts/setup-db.php',
    'setup_database.php' => 'scripts/setup_database.php',
    'test_login.php' => 'scripts/test_login.php',
    'test_logout.php' => 'scripts/test_logout.php',
    'test_logout_button.php' => 'scripts/test_logout_button.php',
    
    // Arquivos públicos
    'adicionar-agendamentos.php' => 'public/adicionar-agendamentos.php',
    'gerar-pdf.php' => 'public/gerar-pdf.php',
    'processar-contato.php' => 'public/processar-contato.php',
    'logout.php' => 'public/logout.php',
];

// Mover arquivos
$count = 0;
foreach ($filesToMove as $source => $destination) {
    if (file_exists($source)) {
        // Criar diretório de destino se não existir
        $destDir = dirname($destination);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        
        // Mover arquivo
        if (rename($source, $destination)) {
            echo "Movido: $source -> $destination\n";
            $count++;
        } else {
            echo "ERRO ao mover: $source\n";
        }
    } else {
        echo "Arquivo não encontrado: $source\n";
    }
}

// Atualizar index.php para apontar para a nova estrutura
if (file_exists('index.php')) {
    $indexContent = file_get_contents('index.php');
    
    // Atualizar caminhos no index.php
    $indexContent = str_replace(
        ['require_once \'config/config.php\';', 'require_once \'config/database.php\';', 'require_once \'helpers/functions.php\';'],
        ['require_once \'config/config.php\';', 'require_once \'config/database.php\';', 'require_once \'helpers/functions.php\';'],
        $indexContent
    );
    
    // Atualizar rotas para os novos caminhos
    $indexContent = str_replace(
        ['/admin-', '/tecnico-'],
        ['/admin/', '/tecnico/'],
        $indexContent
    );
    
    file_put_contents('index.php', $indexContent);
    echo "Atualizado: index.php\n";
}

// Criar arquivo .htaccess para redirecionar URLs antigas
$htaccessContent = <<<EOT
# Redirecionar URLs antigas para a nova estrutura
RewriteEngine On

# Redirecionar arquivos admin
RewriteRule ^admin-([a-z-]+)\.php$ /admin/\$1.php [R=301,L]

# Redirecionar arquivos técnico
RewriteRule ^tecnico-([a-z-]+)\.php$ /tecnico/\$1.php [R=301,L]

# Manter compatibilidade com URLs existentes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
EOT;

file_put_contents('public/.htaccess', $htaccessContent);
echo "Criado: public/.htaccess\n";

echo "\nReorganização concluída! $count arquivos foram movidos.\n";
echo "Lembre-se de atualizar quaisquer referências a esses arquivos em seu código.\n";
?>