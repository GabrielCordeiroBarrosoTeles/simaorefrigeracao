<?php
/**
 * Script para organizar os arquivos e diretórios do sistema
 */

// Definir diretórios que serão criados (se não existirem)
$directories = [
    'admin',
    'controllers/Admin',
    'models',
    'public',
    'public/js',
    'public/css',
    'public/img',
    'config',
    'includes',
    'views/admin/includes',
    'views/admin/layouts',
    'views/admin/partials',
];

// Criar diretórios
echo "<h2>Criando diretórios...</h2>";
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "Diretório criado: $dir<br>";
        } else {
            echo "Erro ao criar diretório: $dir<br>";
        }
    } else {
        echo "Diretório já existe: $dir<br>";
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
      => 'public/css/admin.css',
    
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

// Mover arquivos
echo "<h2>Movendo arquivos...</h2>";
foreach ($file_mapping as $old => $new) {
    if (file_exists($old)) {
        // Criar diretório de destino se não existir
        $dir = dirname($new);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
            echo "Diretório criado: $dir<br>";
        }
        
        // Copiar o arquivo
        if (copy($old, $new)) {
            echo "Arquivo copiado: $old -> $new<br>";
            // Não remover o arquivo original ainda para segurança
            // unlink($old);
        } else {
            echo "Erro ao copiar arquivo: $old -> $new<br>";
        }
    } else {
        echo "Arquivo não encontrado: $old<br>";
    }
}

// Criar arquivo de layout mestre para admin
echo "<h2>Criando arquivos de template...</h2>";

$admin_layout = <<<'EOT'
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Painel Administrativo' ?> - Simão Refrigeração</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/public/css/admin.css" rel="stylesheet">
    
    <?php if (isset($extra_css)): ?>
        <?= $extra_css ?>
    <?php endif; ?>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include 'views/admin/partials/sidebar.php'; ?>
        
        <!-- Content -->
        <div class="content-wrapper">
            <!-- Header -->
            <?php include 'views/admin/partials/header.php'; ?>
            
            <!-- Main Content -->
            <div class="container-fluid">
                <?php include $content_view; ?>
            </div>
            
            <!-- Footer -->
            <?php include 'views/admin/partials/footer.php'; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/public/js/admin.js"></script>
    
    <?php if (isset($extra_js)): ?>
        <?= $extra_js ?>
    <?php endif; ?>
</body>
</html>
EOT;

file_put_contents('views/admin/layouts/master.php', $admin_layout);
echo "Arquivo criado: views/admin/layouts/master.php<br>";

// Criar partials
$admin_header = <<<'EOT'
<header class="main-header">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/admin/dashboard.php">Simão Refrigeração</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?= $_SESSION['user_name'] ?? 'Usuário' ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/admin/profile.php"><i class="fas fa-user"></i> Perfil</a></li>
                            <li><a class="dropdown-item" href="/admin/settings.php"><i class="fas fa-cog"></i> Configurações</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
EOT;

file_put_contents('views/admin/partials/header.php', $admin_header);
echo "Arquivo criado: views/admin/partials/header.php<br>";

$admin_sidebar = <<<'EOT'
<nav id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <h3>Painel Admin</h3>
    </div>

    <ul class="list-unstyled components">
        <li class="<?= $page_title === 'Dashboard' ? 'active' : '' ?>">
            <a href="/admin/dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="<?= $page_title === 'Agendamentos' ? 'active' : '' ?>">
            <a href="#agendamentosSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-calendar-alt"></i> Agendamentos
            </a>
            <ul class="collapse list-unstyled <?= strpos($page_title, 'Agendamentos') !== false ? 'show' : '' ?>" id="agendamentosSubmenu">
                <li>
                    <a href="/admin/agendamentos.php">Listar Todos</a>
                </li>
                <li>
                    <a href="/admin/calendario.php">Calendário</a>
                </li>
            </ul>
        </li>
        <li class="<?= $page_title === 'Clientes' ? 'active' : '' ?>">
            <a href="/admin/clientes.php">
                <i class="fas fa-users"></i> Clientes
            </a>
        </li>
        <li class="<?= $page_title === 'Técnicos' ? 'active' : '' ?>">
            <a href="/admin/tecnicos.php">
                <i class="fas fa-user-hard-hat"></i> Técnicos
            </a>
        </li>
        <li class="<?= $page_title === 'Serviços' ? 'active' : '' ?>">
            <a href="/admin/servicos.php">
                <i class="fas fa-tools"></i> Serviços
            </a>
        </li>
        <li class="<?= $page_title === 'Contatos' ? 'active' : '' ?>">
            <a href="/admin/contatos.php">
                <i class="fas fa-envelope"></i> Contatos
            </a>
        </li>
        <li class="<?= $page_title === 'Depoimentos' ? 'active' : '' ?>">
            <a href="/admin/depoimentos.php">
                <i class="fas fa-comments"></i> Depoimentos
            </a>
        </li>
        <li class="<?= $page_title === 'Estatísticas' ? 'active' : '' ?>">
            <a href="/admin/estatisticas.php">
                <i class="fas fa-chart-bar"></i> Estatísticas
            </a>
        </li>
        <li class="<?= $page_title === 'Configurações' ? 'active' : '' ?>">
            <a href="/admin/settings.php">
                <i class="fas fa-cog"></i> Configurações
            </a>
        </li>
    </ul>
</nav>
EOT;

file_put_contents('views/admin/partials/sidebar.php', $admin_sidebar);
echo "Arquivo criado: views/admin/partials/sidebar.php<br>";

$admin_footer = <<<'EOT'
<footer class="main-footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <p>&copy; <?= date('Y') ?> Simão Refrigeração. Todos os direitos reservados.</p>
            </div>
            <div class="col-md-6 text-end">
                <p>Versão 1.0</p>
            </div>
        </div>
    </div>
</footer>
EOT;

file_put_contents('views/admin/partials/footer.php', $admin_footer);
echo "Arquivo criado: views/admin/partials/footer.php<br>";

// Criar um arquivo de template helper
$template_helper = <<<'EOT'
<?php
/**
 * Funções auxiliares para o sistema de templates
 */

/**
 * Renderiza uma view com o layout mestre
 * 
 * @param string $view Caminho da view a ser renderizada
 * @param array $data Dados a serem passados para a view
 * @param string $layout Layout a ser utilizado
 * @return void
 */
function render_view($view, $data = [], $layout = 'master') {
    // Extrair variáveis para a view
    extract($data);
    
    // Definir o caminho da view
    $content_view = $view;
    
    // Incluir o layout
    include "views/admin/layouts/{$layout}.php";
}

/**
 * Renderiza uma view sem layout
 * 
 * @param string $view Caminho da view a ser renderizada
 * @param array $data Dados a serem passados para a view
 * @return void
 */
function render_partial($view, $data = []) {
    // Extrair variáveis para a view
    extract($data);
    
    // Incluir a view
    include $view;
}
EOT;

file_put_contents('includes/template_helper.php', $template_helper);
echo "Arquivo criado: includes/template_helper.php<br>";

echo "<h2>Organização concluída!</h2>";
echo "<p>Os arquivos foram organizados com sucesso. Agora você pode começar a usar o novo sistema de templates.</p>";
echo "<p>Para usar o sistema de templates, inclua o arquivo 'includes/template_helper.php' e use a função render_view().</p>";
echo "<p>Exemplo:</p>";
echo "<pre>
require_once 'includes/template_helper.php';

\$page_title = 'Dashboard';
\$data = [
    'page_title' => \$page_title,
    'stats' => \$stats,
    // outros dados...
];

render_view('views/admin/dashboard_content.php', \$data);
</pre>";
?>
