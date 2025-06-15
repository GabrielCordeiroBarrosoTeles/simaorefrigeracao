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

// Conectar ao banco de dados
$db = db_connect();

// Obter a tabela a ser exibida
$table = isset($_GET['table']) ? sanitize($_GET['table']) : '';

// Verificar se a tabela é válida
$valid_tables = ['clientes', 'tecnicos', 'servicos', 'agendamentos', 'depoimentos', 'contatos', 'usuarios', 'configuracoes', 'estatisticas'];
if (!in_array($table, $valid_tables)) {
    set_flash_message('danger', 'Tabela inválida.');
    redirect('/admin-dashboard.php');
}

// Definir título e colunas para cada tabela
$table_config = [
    'clientes' => [
        'title' => 'Clientes',
        'icon' => 'users',
        'columns' => ['ID', 'Nome', 'Email', 'Telefone', 'Tipo', 'Cidade/UF', 'Data de Cadastro', 'Ações'],
        'query' => "SELECT * FROM clientes ORDER BY id DESC",
        'new_url' => 'admin-form.php?form=cliente',
        'edit_url' => 'admin-form.php?form=cliente&id=',
        'delete_url' => 'admin-delete.php?table=clientes&id='
    ],
    'tecnicos' => [
        'title' => 'Técnicos',
        'icon' => 'user-hard-hat',
        'columns' => ['ID', 'Nome', 'Email', 'Telefone', 'Especialidade', 'Status', 'Ações'],
        'query' => "SELECT * FROM tecnicos ORDER BY id DESC",
        'new_url' => 'admin-form.php?form=tecnico',
        'edit_url' => 'admin-form.php?form=tecnico&id=',
        'delete_url' => 'admin-delete.php?table=tecnicos&id='
    ],
    'servicos' => [
        'title' => 'Serviços',
        'icon' => 'tools',
        'columns' => ['ID', 'Título', 'Ícone', 'Descrição', 'Ações'],
        'query' => "SELECT * FROM servicos ORDER BY id DESC",
        'new_url' => 'admin-form.php?form=servico',
        'edit_url' => 'admin-form.php?form=servico&id=',
        'delete_url' => 'admin-delete.php?table=servicos&id='
    ],
    'agendamentos' => [
        'title' => 'Agendamentos',
        'icon' => 'calendar-check',
        'columns' => ['ID', 'Título', 'Cliente', 'Serviço', 'Técnico', 'Data', 'Horário', 'Status', 'Ações'],
        'query' => "SELECT a.*, c.nome as cliente_nome, s.titulo as servico_nome, t.nome as tecnico_nome 
                   FROM agendamentos a 
                   LEFT JOIN clientes c ON a.cliente_id = c.id 
                   LEFT JOIN servicos s ON a.servico_id = s.id 
                   LEFT JOIN tecnicos t ON a.tecnico_id = t.id 
                   ORDER BY a.data_agendamento DESC",
        'new_url' => 'admin-form.php?form=agendamento',
        'edit_url' => 'admin-form.php?form=agendamento&id=',
        'delete_url' => 'admin-delete.php?table=agendamentos&id='
    ],
    'depoimentos' => [
        'title' => 'Depoimentos',
        'icon' => 'comments',
        'columns' => ['ID', 'Nome', 'Tipo', 'Texto', 'Status', 'Ações'],
        'query' => "SELECT * FROM depoimentos ORDER BY id DESC",
        'new_url' => 'admin-form.php?form=depoimento',
        'edit_url' => 'admin-form.php?form=depoimento&id=',
        'delete_url' => 'admin-delete.php?table=depoimentos&id='
    ],
    'contatos' => [
        'title' => 'Contatos',
        'icon' => 'envelope',
        'columns' => ['ID', 'Nome', 'Email', 'Telefone', 'Serviço', 'Status', 'Data', 'Ações'],
        'query' => "SELECT c.*, s.titulo as servico_nome 
                   FROM contatos c 
                   LEFT JOIN servicos s ON c.servico_id = s.id 
                   ORDER BY c.data_criacao DESC",
        'new_url' => '',
        'edit_url' => 'admin-form.php?form=contato&id=',
        'delete_url' => 'admin-delete.php?table=contatos&id='
    ],
    'usuarios' => [
        'title' => 'Usuários',
        'icon' => 'user-shield',
        'columns' => ['ID', 'Nome', 'Email', 'Nível', 'Último Login', 'Ações'],
        'query' => "SELECT * FROM usuarios ORDER BY id DESC",
        'new_url' => 'admin-form.php?form=usuario',
        'edit_url' => 'admin-form.php?form=usuario&id=',
        'delete_url' => 'admin-delete.php?table=usuarios&id='
    ],
    'configuracoes' => [
        'title' => 'Configurações',
        'icon' => 'cogs',
        'columns' => ['ID', 'Nome da Empresa', 'Email', 'Telefone', 'Última Atualização', 'Ações'],
        'query' => "SELECT * FROM configuracoes ORDER BY id DESC",
        'new_url' => '',
        'edit_url' => 'admin-form.php?form=configuracao&id=',
        'delete_url' => ''
    ],
    'estatisticas' => [
        'title' => 'Estatísticas',
        'icon' => 'chart-bar',
        'columns' => ['ID', 'Valor', 'Descrição', 'Ordem', 'Ações'],
        'query' => "SELECT * FROM estatisticas ORDER BY ordem ASC",
        'new_url' => 'admin-form.php?form=estatistica',
        'edit_url' => 'admin-form.php?form=estatistica&id=',
        'delete_url' => 'admin-delete.php?table=estatisticas&id='
    ]
];

// Obter dados da tabela
try {
    $query = $table_config[$table]['query'];
    $stmt = $db->prepare($query);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    set_flash_message('danger', 'Erro ao buscar dados: ' . $e->getMessage());
    $rows = [];
}

// Título da página
$page_title = $table_config[$table]['title'];
$page_icon = $table_config[$table]['icon'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Painel Administrativo | <?= SITE_NAME ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include 'views/admin/includes/sidebar.php'; ?>
        
        <!-- Content -->
        <div class="content-wrapper">
            <!-- Header -->
            <?php include 'views/admin/includes/header.php'; ?>
            
            <!-- Main Content -->
            <div class="container-fluid">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-<?= $page_icon ?> mr-2"></i> <?= $page_title ?>
                    </h1>
                    <?php if (!empty($table_config[$table]['new_url'])): ?>
                    <a href="<?= $table_config[$table]['new_url'] ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-plus fa-sm text-white-50"></i> Novo <?= rtrim($page_title, 's') ?>
                    </a>
                    <?php endif; ?>
                </div>
                
                <?php display_flash_message(); ?>
                
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Lista de <?= $page_title ?></h6>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" id="refreshTable">
                                <i class="fas fa-sync-alt"></i> Atualizar
                            </button>
                            <?php if ($table === 'agendamentos'): ?>
                            <a href="admin-calendario.php" class="btn btn-sm btn-outline-info ml-2">
                                <i class="fas fa-calendar-alt"></i> Ver Calendário
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="dataTable-<?= $table ?>" width="100%" cellspacing="0">
                                <thead class="thead-light">
                                    <tr>
                                        <?php foreach ($table_config[$table]['columns'] as $column): ?>
                                        <th><?= $column ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($rows)): ?>
                                    <tr>
                                        <td colspan="<?= count($table_config[$table]['columns']) ?>" class="text-center">
                                            Nenhum registro encontrado.
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($rows as $row): ?>
                                        <tr>
                                            <?php if ($table === 'clientes'): ?>
                                                <td><?= $row['id'] ?></td>
                                                <td><?= $row['nome'] ?></td>
                                                <td><?= $row['email'] ?></td>
                                                <td><?= $row['telefone'] ?></td>
                                                <td>
                                                    <?php
                                                    $tipo_class = 'secondary';
                                                    switch ($row['tipo']) {
                                                        case 'residencial':
                                                            $tipo_class = 'info';
                                                            break;
                                                        case 'comercial':
                                                            $tipo_class = 'primary';
                                                            break;
                                                        case 'industrial':
                                                            $tipo_class = 'warning';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge badge-<?= $tipo_class ?>">
                                                        <?= ucfirst($row['tipo']) ?>
                                                    </span>
                                                </td>
                                                <td><?= $row['cidade'] ?>/<?= $row['estado'] ?></td>
                                                <td><?= format_date($row['data_criacao']) ?></td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="<?= $table_config[$table]['edit_url'] . $row['id'] ?>" class="btn btn-sm btn-primary" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="<?= $table_config[$table]['delete_url'] . $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este registro?')" title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            <?php elseif ($table === 'tecnicos'): ?>
                                                <td><?= $row['id'] ?></td>
                                                <td><i class="fas fa-user-hard-hat mr-1"></i> <?= $row['nome'] ?></td>
                                                <td><?= $row['email'] ?></td>
                                                <td><?= $row['telefone'] ?></td>
                                                <td><?= $row['especialidade'] ?></td>
                                                <td>
                                                    <?php if ($row['status'] === 'ativo'): ?>
                                                        <span class="badge badge-success">Ativo</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Inativo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="<?= $table_config[$table]['edit_url'] . $row['id'] ?>" class="btn btn-sm btn-primary" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="<?= $table_config[$table]['delete_url'] . $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este registro?')" title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            <?php elseif ($table === 'servicos'): ?>
                                                <td><?= $row['id'] ?></td>
                                                <td><?= $row['titulo'] ?></td>
                                                <td><i class="fas fa-<?= $row['icone'] ?>"></i> <?= $row['icone'] ?></td>
                                                <td><?= truncate($row['descricao'], 100) ?></td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="<?= $table_config[$table]['edit_url'] . $row['id'] ?>" class="btn btn-sm btn-primary" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="<?= $table_config[$table]['delete_url'] . $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este registro?')" title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            <?php elseif ($table === 'agendamentos'): ?>
                                                <td><?= $row['id'] ?></td>
                                                <td><?= $row['titulo'] ?></td>
                                                <td><?= $row['cliente_nome'] ?></td>
                                                <td><?= $row['servico_nome'] ?></td>
                                                <td><?= $row['tecnico_nome'] ?></td>
                                                <td><?= format_date($row['data_agendamento']) ?></td>
                                                <td><?= $row['hora_inicio'] ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = 'secondary';
                                                    switch ($row['status']) {
                                                        case 'pendente':
                                                            $status_class = 'warning';
                                                            break;
                                                        case 'concluido':
                                                            $status_class = 'success';
                                                            break;
                                                        case 'cancelado':
                                                            $status_class = 'danger';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge badge-<?= $status_class ?>">
                                                        <?= ucfirst($row['status']) ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="<?= $table_config[$table]['edit_url'] . $row['id'] ?>" class="btn btn-sm btn-primary" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="<?= $table_config[$table]['delete_url'] . $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este registro?')" title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            <?php elseif ($table === 'depoimentos'): ?>
                                                <td><?= $row['id'] ?></td>
                                                <td><?= $row['nome'] ?></td>
                                                <td><?= $row['tipo'] ?></td>
                                                <td><?= truncate($row['texto'], 100) ?></td>
                                                <td>
                                                    <?php if ($row['ativo']): ?>
                                                        <span class="badge badge-success">Ativo</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Inativo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="<?= $table_config[$table]['edit_url'] . $row['id'] ?>" class="btn btn-sm btn-primary" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="<?= $table_config[$table]['delete_url'] . $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este registro?')" title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            <?php elseif ($table === 'contatos'): ?>
                                                <td><?= $row['id'] ?></td>
                                                <td><?= $row['nome'] ?></td>
                                                <td><?= $row['email'] ?></td>
                                                <td><?= $row['telefone'] ?></td>
                                                <td><?= $row['servico_nome'] ?? 'Não especificado' ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = 'secondary';
                                                    switch ($row['status']) {
                                                        case 'novo':
                                                            $status_class = 'danger';
                                                            break;
                                                        case 'em_andamento':
                                                            $status_class = 'warning';
                                                            break;
                                                        case 'respondido':
                                                            $status_class = 'info';
                                                            break;
                                                        case 'finalizado':
                                                            $status_class = 'success';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge badge-<?= $status_class ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $row['status'])) ?>
                                                    </span>
                                                </td>
                                                <td><?= format_date($row['data_criacao'], 'd/m/Y H:i') ?></td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="<?= $table_config[$table]['edit_url'] . $row['id'] ?>" class="btn btn-sm btn-primary" title="Visualizar">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="<?= $table_config[$table]['delete_url'] . $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este registro?')" title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            <?php elseif ($table === 'usuarios'): ?>
                                                <td><?= $row['id'] ?></td>
                                                <td><?= $row['nome'] ?></td>
                                                <td><?= $row['email'] ?></td>
                                                <td>
                                                    <?php
                                                    $nivel_class = 'secondary';
                                                    switch ($row['nivel']) {
                                                        case 'admin':
                                                            $nivel_class = 'danger';
                                                            break;
                                                        case 'editor':
                                                            $nivel_class = 'primary';
                                                            break;
                                                        case 'tecnico':
                                                            $nivel_class = 'info';
                                                            break;
                                                        case 'tecnico_adm':
                                                            $nivel_class = 'warning';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge badge-<?= $nivel_class ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $row['nivel'])) ?>
                                                    </span>
                                                </td>
                                                <td><?= $row['ultimo_login'] ? format_date($row['ultimo_login'], 'd/m/Y H:i') : 'Nunca' ?></td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="<?= $table_config[$table]['edit_url'] . $row['id'] ?>" class="btn btn-sm btn-primary" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                                        <a href="<?= $table_config[$table]['delete_url'] . $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este usuário?')" title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            <?php elseif ($table === 'configuracoes'): ?>
                                                <td><?= $row['id'] ?></td>
                                                <td><?= $row['nome_empresa'] ?></td>
                                                <td><?= $row['email'] ?></td>
                                                <td><?= $row['telefone'] ?></td>
                                                <td><?= $row['data_atualizacao'] ? format_date($row['data_atualizacao'], 'd/m/Y H:i') : 'Nunca' ?></td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="<?= $table_config[$table]['edit_url'] . $row['id'] ?>" class="btn btn-sm btn-primary" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            <?php elseif ($table === 'estatisticas'): ?>
                                                <td><?= $row['id'] ?></td>
                                                <td><?= $row['valor'] ?></td>
                                                <td><?= $row['descricao'] ?></td>
                                                <td><?= $row['ordem'] ?></td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="<?= $table_config[$table]['edit_url'] . $row['id'] ?>" class="btn btn-sm btn-primary" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="<?= $table_config[$table]['delete_url'] . $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este registro?')" title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <?php include 'views/admin/includes/footer.php'; ?>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/admin.js"></script>
    
    <script>
        $(document).ready(function() {
            // Inicializar DataTable com ID único para evitar o erro de reinicialização
            const tableId = 'dataTable-<?= $table ?>';
            if ($('#' + tableId).length > 0) {
                $('#' + tableId).DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
                    },
                    responsive: true,
                    pageLength: 25,
                    order: [[0, 'desc']]
                });
            }
            
            // Botão de atualizar tabela
            $('#refreshTable').on('click', function() {
                location.reload();
            });
        });
    </script>
</body>
</html>
