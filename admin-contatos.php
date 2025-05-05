<?php
require_once 'bootstrap.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
    redirect('/admin/login');
}

// Conexão com o banco de dados
$db = db_connect();

// Processar ações
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($action === 'view' && $id > 0) {
        try {
            // Marcar como lido
            $query = "UPDATE contatos SET status = 'lido' WHERE id = :id AND status = 'novo'";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            // Buscar contato
            $query = "SELECT * FROM contatos WHERE id = :id LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $contato = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$contato) {
                set_flash_message('danger', 'Contato não encontrado.');
                redirect('/admin-contatos.php');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação.');
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
            redirect('/admin-contatos.php');
        }
    }
    
    if ($action === 'responder' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $resposta = sanitize($_POST['resposta'] ?? '');
        
        if (empty($resposta)) {
            set_flash_message('danger', 'Por favor, digite uma resposta.');
            redirect('/admin-contatos.php?action=view&id=' . $id);
        }
        
        try {
            $query = "UPDATE contatos SET status = 'respondido', resposta = :resposta, data_resposta = NOW() WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':resposta', $resposta);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                // Buscar dados do contato para envio de e-mail
                $query = "SELECT * FROM contatos WHERE id = :id LIMIT 1";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                $contato = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Enviar e-mail de resposta (implementação futura)
                // ...
                
                set_flash_message('success', 'Resposta enviada com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao enviar resposta.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação.');
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/admin-contatos.php');
    }
    
    if ($action === 'excluir' && $id > 0) {
        try {
            $query = "DELETE FROM contatos WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Contato excluído com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao excluir contato.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação.');
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/admin-contatos.php');
    }
}

// Buscar todos os contatos
$contatos = [];
try {
    $query = "SELECT * FROM contatos ORDER BY data_criacao DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $contatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    set_flash_message('danger', 'Erro ao buscar contatos.');
    if (DEBUG_MODE) {
        $_SESSION['error_details'] = $e->getMessage();
    }
}

// Incluir cabeçalho
include 'views/admin/includes/header.php';
?>

<div class="container-fluid px-4">
    <?php if (isset($action) && $action === 'view' && isset($contato)): ?>
        <h1 class="mt-4">Visualizar Contato</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="admin-dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="admin-contatos.php">Contatos</a></li>
            <li class="breadcrumb-item active">Visualizar</li>
        </ol>
        
        <?php display_flash_message(); ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-envelope me-1"></i>
                        Mensagem de <?= htmlspecialchars($contato['nome']) ?>
                    </div>
                    <div>
                        <a href="admin-contatos.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h5>Informações do Contato</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 150px">Nome:</th>
                                <td><?= htmlspecialchars($contato['nome']) ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?= htmlspecialchars($contato['email']) ?></td>
                            </tr>
                            <tr>
                                <th>Telefone:</th>
                                <td><?= htmlspecialchars($contato['telefone'] ?? 'Não informado') ?></td>
                            </tr>
                            <tr>
                                <th>Assunto:</th>
                                <td><?= htmlspecialchars($contato['assunto'] ?? 'Não informado') ?></td>
                            </tr>
                            <tr>
                                <th>Data:</th>
                                <td><?= date('d/m/Y H:i', strtotime($contato['data_criacao'])) ?></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <?php
                                    $status_class = 'bg-info';
                                    $status_text = 'Lido';
                                    
                                    if ($contato['status'] === 'novo') {
                                        $status_class = 'bg-warning text-dark';
                                        $status_text = 'Novo';
                                    } elseif ($contato['status'] === 'respondido') {
                                        $status_class = 'bg-success';
                                        $status_text = 'Respondido';
                                    }
                                    ?>
                                    <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Mensagem</h5>
                        <div class="card">
                            <div class="card-body bg-light">
                                <p><?= nl2br(htmlspecialchars($contato['mensagem'])) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($contato['status'] === 'respondido'): ?>
                    <div class="row">
                        <div class="col-12">
                            <h5>Resposta</h5>
                            <div class="card">
                                <div class="card-body bg-light">
                                    <p><?= nl2br(htmlspecialchars($contato['resposta'])) ?></p>
                                    <small class="text-muted">Respondido em: <?= date('d/m/Y H:i', strtotime($contato['data_resposta'])) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <div class="col-12">
                            <h5>Responder</h5>
                            <form method="post" action="admin-contatos.php?action=responder&id=<?= $contato['id'] ?>">
                                <div class="mb-3">
                                    <textarea class="form-control" name="resposta" rows="5" placeholder="Digite sua resposta..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Enviar Resposta</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <h1 class="mt-4">Gerenciar Contatos</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="admin-dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Contatos</li>
        </ol>
        
        <?php display_flash_message(); ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i>
                Lista de Contatos
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Assunto</th>
                                <th>Mensagem</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($contatos)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Nenhum contato encontrado.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($contatos as $contato): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($contato['nome']) ?></td>
                                        <td><?= htmlspecialchars($contato['email']) ?></td>
                                        <td><?= htmlspecialchars($contato['assunto'] ?? 'Não informado') ?></td>
                                        <td><?= truncate(htmlspecialchars($contato['mensagem']), 100) ?></td>
                                        <td>
                                            <?php
                                            $status_class = 'bg-info';
                                            $status_text = 'Lido';
                                            
                                            if ($contato['status'] === 'novo') {
                                                $status_class = 'bg-warning text-dark';
                                                $status_text = 'Novo';
                                            } elseif ($contato['status'] === 'respondido') {
                                                $status_class = 'bg-success';
                                                $status_text = 'Respondido';
                                            }
                                            ?>
                                            <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($contato['data_criacao'])) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="admin-contatos.php?action=view&id=<?= $contato['id'] ?>" class="btn btn-sm btn-primary" title="Visualizar">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="admin-contatos.php?action=excluir&id=<?= $contato['id'] ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este contato?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'views/admin/includes/footer.php'; ?>
