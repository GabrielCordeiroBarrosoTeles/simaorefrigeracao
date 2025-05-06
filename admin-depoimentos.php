<?php
require_once 'bootstrap.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
    redirect('admin-login.php');
}

// Conexão com o banco de dados
$db = db_connect();

// Processar ações
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Processar exclusão
if ($action === 'delete' && $id > 0) {
    try {
        $query = "DELETE FROM depoimentos WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            set_flash_message('success', 'Depoimento excluído com sucesso!');
        } else {
            set_flash_message('danger', 'Erro ao excluir depoimento.');
        }
    } catch (PDOException $e) {
        set_flash_message('danger', 'Erro ao processar sua solicitação.');
        if (DEBUG_MODE) {
            $_SESSION['error_details'] = $e->getMessage();
        }
    }
    
    redirect('admin-depoimentos.php');
}

// Processar alteração de status
if ($action === 'toggle' && $id > 0) {
    try {
        // Primeiro, buscar o status atual
        $query = "SELECT ativo FROM depoimentos WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $depoimento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($depoimento) {
            // Inverter o status
            $novo_status = $depoimento['ativo'] ? 0 : 1;
            
            $query = "UPDATE depoimentos SET ativo = :ativo WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':ativo', $novo_status);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $status_text = $novo_status ? 'ativado' : 'desativado';
                set_flash_message('success', "Depoimento {$status_text} com sucesso!");
            } else {
                set_flash_message('danger', 'Erro ao atualizar status do depoimento.');
            }
        } else {
            set_flash_message('danger', 'Depoimento não encontrado.');
        }
    } catch (PDOException $e) {
        set_flash_message('danger', 'Erro ao processar sua solicitação.');
        if (DEBUG_MODE) {
            $_SESSION['error_details'] = $e->getMessage();
        }
    }
    
    redirect('admin-depoimentos.php');
}

// Buscar todos os depoimentos
$depoimentos = [];
try {
    $query = "SELECT d.*, c.nome as cliente_nome 
              FROM depoimentos d
              LEFT JOIN clientes c ON d.cliente_id = c.id
              ORDER BY d.data_criacao DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $depoimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    set_flash_message('danger', 'Erro ao buscar depoimentos.');
    if (DEBUG_MODE) {
        $_SESSION['error_details'] = $e->getMessage();
    }
}

// Título da página
$page_title = 'Depoimentos';

// Incluir o cabeçalho
$page_title = 'Depoimentos';
include 'views/admin/includes/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Depoimentos</h1>
        <a href="admin-form.php?table=depoimentos&action=create" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Novo Depoimento
        </a>
    </div>
    
    <?php display_flash_message(); ?>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Todos os Depoimentos</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Avaliação</th>
                            <th>Depoimento</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($depoimentos)): ?>
                            <?php foreach ($depoimentos as $depoimento): ?>
                                <tr>
                                    <td><?= htmlspecialchars($depoimento['cliente_nome'] ?? 'Cliente Anônimo') ?></td>
                                    <td>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= $depoimento['avaliacao'] ? 'text-warning' : 'text-muted' ?>"></i>
                                        <?php endfor; ?>
                                    </td>
                                    <td><?= htmlspecialchars(substr($depoimento['texto'], 0, 100)) . (strlen($depoimento['texto']) > 100 ? '...' : '') ?></td>
                                    <td><?= date('d/m/Y', strtotime($depoimento['data_criacao'])) ?></td>
                                    <td>
                                        <?php if ($depoimento['ativo']): ?>
                                            <span class="badge badge-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="admin-form.php?table=depoimentos&action=edit&id=<?= $depoimento['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="admin-depoimentos.php?action=toggle&id=<?= $depoimento['id'] ?>" class="btn btn-sm <?= $depoimento['ativo'] ? 'btn-secondary' : 'btn-success' ?>">
                                            <i class="fas <?= $depoimento['ativo'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                        </a>
                                        <a href="admin-depoimentos.php?action=delete&id=<?= $depoimento['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este depoimento?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Nenhum depoimento encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'views/admin/includes/footer.php'; ?>
