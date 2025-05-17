<?php
require_once 'bootstrap.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
    redirect('/admin-login.php');
}

// Obter parâmetros
$form = isset($_GET['form']) ? $_GET['form'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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
                    redirect('/admin-clientes.php');
                }
            } catch (PDOException $e) {
                set_flash_message('danger', 'Erro ao buscar cliente.');
                redirect('/admin-clientes.php');
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
                    redirect('/admin-tecnicos.php');
                }
            } catch (PDOException $e) {
                set_flash_message('danger', 'Erro ao buscar técnico.');
                redirect('/admin-tecnicos.php');
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
                    redirect('/admin-servicos.php');
                }
            } catch (PDOException $e) {
                set_flash_message('danger', 'Erro ao buscar serviço.');
                redirect('/admin-servicos.php');
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
                    redirect('/admin-agendamentos.php');
                }
            } catch (PDOException $e) {
                set_flash_message('danger', 'Erro ao buscar agendamento.');
                redirect('/admin-agendamentos.php');
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
            set_flash_message('danger', 'Erro ao buscar dados para o formulário.');
            redirect('/admin-agendamentos.php');
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
