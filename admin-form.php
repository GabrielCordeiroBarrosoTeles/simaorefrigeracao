<?php
require_once 'bootstrap.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
    redirect('/admin-login.php');
}

// Obter parâmetros
$form = isset($_GET['form']) ? $_GET['form'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Definir ação com base na presença do ID
$action = ($id > 0) ? 'edit' : 'create';

// Conectar ao banco de dados
$db = db_connect();

// Definir título da página
$page_title = 'Formulário';

// Processar formulário específico
switch ($form) {
    case 'cliente':
        $page_title = ($action === 'edit') ? 'Editar Cliente' : 'Novo Cliente';
        $page_icon = 'user';
        
        // Buscar dados do cliente para edição
        $cliente = null;
        if ($action === 'edit' && $id > 0) {
            try {
                $stmt = $db->prepare("SELECT * FROM clientes WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$cliente) {
                    set_flash_message('danger', 'Cliente não encontrado.');
                    redirect('/admin-table.php?table=clientes');
                }
            } catch (PDOException $e) {
                set_flash_message('danger', 'Erro ao buscar cliente: ' . $e->getMessage());
                redirect('/admin-table.php?table=clientes');
            }
        }
        break;
    
    case 'tecnico':
        $page_title = ($action === 'edit') ? 'Editar Técnico' : 'Novo Técnico';
        $page_icon = 'user-tie';
        
        // Buscar dados do técnico para edição
        $tecnico = null;
        if ($action === 'edit' && $id > 0) {
            try {
                $stmt = $db->prepare("SELECT * FROM tecnicos WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$tecnico) {
                    set_flash_message('danger', 'Técnico não encontrado.');
                    redirect('/admin-table.php?table=tecnicos');
                }
            } catch (PDOException $e) {
                set_flash_message('danger', 'Erro ao buscar técnico: ' . $e->getMessage());
                redirect('/admin-table.php?table=tecnicos');
            }
        }
        break;
    
    case 'servico':
        $page_title = ($action === 'edit') ? 'Editar Serviço' : 'Novo Serviço';
        $page_icon = 'tools';
        
        // Buscar dados do serviço para edição
        $servico = null;
        if ($action === 'edit' && $id > 0) {
            try {
                $stmt = $db->prepare("SELECT * FROM servicos WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $servico = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$servico) {
                    set_flash_message('danger', 'Serviço não encontrado.');
                    redirect('/admin-table.php?table=servicos');
                }
            } catch (PDOException $e) {
                set_flash_message('danger', 'Erro ao buscar serviço: ' . $e->getMessage());
                redirect('/admin-table.php?table=servicos');
            }
        }
        break;
    
    case 'agendamento':
        $page_title = ($action === 'edit') ? 'Editar Agendamento' : 'Novo Agendamento';
        $page_icon = 'calendar-alt';
        
        // Buscar dados do agendamento para edição
        $agendamento = null;
        if ($action === 'edit' && $id > 0) {
            try {
                $stmt = $db->prepare("SELECT * FROM agendamentos WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$agendamento) {
                    set_flash_message('danger', 'Agendamento não encontrado.');
                    redirect('/admin-table.php?table=agendamentos');
                }
            } catch (PDOException $e) {
                set_flash_message('danger', 'Erro ao buscar agendamento: ' . $e->getMessage());
                redirect('/admin-table.php?table=agendamentos');
            }
        }
        
        // Buscar clientes, serviços e técnicos para o formulário
        try {
            $stmt = $db->prepare("SELECT id, nome FROM clientes ORDER BY nome");
            $stmt->execute();
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $db->prepare("SELECT id, titulo FROM servicos ORDER BY titulo");
            $stmt->execute();
            $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $db->prepare("SELECT id, nome FROM tecnicos WHERE status = 'ativo' ORDER BY nome");
            $stmt->execute();
            $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao buscar dados para o formulário: ' . $e->getMessage());
            redirect('/admin-table.php?table=agendamentos');
        }
        break;
    
    case 'depoimento':
        $page_title = ($action === 'edit') ? 'Editar Depoimento' : 'Novo Depoimento';
        $page_icon = 'comment';
        
        // Buscar dados do depoimento para edição
        $depoimento = null;
        if ($action === 'edit' && $id > 0) {
            try {
                $stmt = $db->prepare("SELECT * FROM depoimentos WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $depoimento = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$depoimento) {
                    set_flash_message('danger', 'Depoimento não encontrado.');
                    redirect('/admin-table.php?table=depoimentos');
                }
            } catch (PDOException $e) {
                set_flash_message('danger', 'Erro ao buscar depoimento: ' . $e->getMessage());
                redirect('/admin-table.php?table=depoimentos');
            }
        }
        break;
    
    case 'usuario':
        $page_title = ($action === 'edit') ? 'Editar Usuário' : 'Novo Usuário';
        $page_icon = 'user-shield';
        
        // Buscar dados do usuário para edição
        $usuario = null;
        if ($action === 'edit' && $id > 0) {
            try {
                $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$usuario) {
                    set_flash_message('danger', 'Usuário não encontrado.');
                    redirect('/admin-table.php?table=usuarios');
                }
            } catch (PDOException $e) {
                set_flash_message('danger', 'Erro ao buscar usuário: ' . $e->getMessage());
                redirect('/admin-table.php?table=usuarios');
            }
        }
        break;
    
    case 'configuracao':
        $page_title = 'Configurações do Sistema';
        $page_icon = 'cogs';
        
        // Buscar dados de configuração
        try {
            $stmt = $db->prepare("SELECT * FROM configuracoes WHERE id = :id");
            $stmt->bindParam(':id', $id > 0 ? $id : 1);
            $stmt->execute();
            $configuracao = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$configuracao) {
                set_flash_message('danger', 'Configurações não encontradas.');
                redirect('/admin-dashboard.php');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao buscar configurações: ' . $e->getMessage());
            redirect('/admin-dashboard.php');
        }
        break;
    
    case 'estatistica':
        $page_title = ($action === 'edit') ? 'Editar Estatística' : 'Nova Estatística';
        $page_icon = 'chart-bar';
        
        // Buscar dados da estatística para edição
        $estatistica = null;
        if ($action === 'edit' && $id > 0) {
            try {
                $stmt = $db->prepare("SELECT * FROM estatisticas WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $estatistica = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$estatistica) {
                    set_flash_message('danger', 'Estatística não encontrada.');
                    redirect('/admin-table.php?table=estatisticas');
                }
            } catch (PDOException $e) {
                set_flash_message('danger', 'Erro ao buscar estatística: ' . $e->getMessage());
                redirect('/admin-table.php?table=estatisticas');
            }
        }
        break;
    
    default:
        set_flash_message('danger', 'Formulário não encontrado.');
        redirect('/admin-dashboard.php');
        break;
}

// Incluir cabeçalho
include 'views/admin/includes/header.php';
?>

<!-- Sidebar -->
<?php include 'views/admin/includes/sidebar.php'; ?>

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">

        <!-- Begin Page Content -->
        <div class="container-fluid">

            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-<?= $page_icon ?? 'edit' ?> mr-2"></i> <?= $page_title ?>
                </h1>
                <a href="javascript:history.back()" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-arrow-left fa-sm text-white-50"></i> Voltar
                </a>
            </div>

            <?php display_flash_message(); ?>

            <!-- Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?= ($action === 'edit') ? 'Editar' : 'Novo' ?> <?= ucfirst($form) ?>
                    </h6>
                </div>
                <div class="card-body">
                    <?php
                    // Incluir formulário específico
                    switch ($form) {
                        case 'cliente':
                            include 'views/admin/forms/cliente.php';
                            break;
                        
                        case 'tecnico':
                            include 'views/admin/forms/tecnico.php';
                            break;
                        
                        case 'servico':
                            include 'views/admin/forms/servico.php';
                            break;
                        
                        case 'agendamento':
                            include 'views/admin/forms/agendamento.php';
                            break;
                            
                        case 'depoimento':
                            include 'views/admin/forms/depoimento.php';
                            break;
                            
                        case 'usuario':
                            include 'views/admin/forms/usuario.php';
                            break;
                            
                        case 'configuracao':
                            include 'views/admin/forms/configuracao.php';
                            break;
                            
                        case 'estatistica':
                            include 'views/admin/forms/estatistica.php';
                            break;
                    }
                    ?>
                </div>
            </div>

        </div>
        <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->

    <!-- Footer -->
    <?php include 'views/admin/includes/footer.php'; ?>
    <!-- End of Footer -->

</div>
<!-- End of Content Wrapper -->