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
    
    if ($action === 'aprovar' && $id > 0) {
        try {
            $query = "UPDATE depoimentos SET aprovado = 1 WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Depoimento aprovado com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao aprovar depoimento.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação.');
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/admin-depoimentos.php');
    }
    
    if ($action === 'reprovar' && $id > 0) {
        try {
            $query = "UPDATE depoimentos SET aprovado = 0 WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Depoimento reprovado com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao reprovar depoimento.');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação.');
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
        }
        
        redirect('/admin-depoimentos.php');
    }
    
    if ($action === 'excluir' && $id > 0) {
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
        
        redirect('/admin-depoimentos.php');
    }
}

// Processar formulário de adição/edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nome = sanitize($_POST['nome'] ?? '');
    $cargo = sanitize($_POST['cargo'] ?? '');
    $empresa = sanitize($_POST['empresa'] ?? '');
    $texto = sanitize($_POST['texto'] ?? '');
    $aprovado = isset($_POST['aprovado']) ? 1 : 0;
    
    if (empty($nome) || empty($texto)) {
        set_flash_message('danger', 'Por favor, preencha os campos obrigatórios.');
        redirect('/admin-depoimentos.php');
    }
    
    try {
        if ($id > 0) {
            // Atualizar depoimento existente
            $query = "UPDATE depoimentos SET nome = :nome, cargo = :cargo, empresa = :empresa, texto = :texto, aprovado = :aprovado WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
        } else {
            // Adicionar novo depoimento
            $query = "INSERT INTO depoimentos (nome, cargo, empresa, texto, aprovado, data_criacao) VALUES (:nome, :cargo, :empresa, :texto, :aprovado, NOW())";
            $stmt = $db->prepare($query);
        }
        
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':cargo', $cargo);
        $stmt->bindParam(':empresa', $empresa);
        $stmt->bindParam(':texto', $texto);
        $stmt->bindParam(':aprovado', $aprovado);
        
        if ($stmt->execute()) {
            set_flash_message('success', ($id > 0 ? 'Depoimento atualizado' : 'Depoimento adicionado') . ' com sucesso!');
        } else {
            set_flash_message('danger', 'Erro ao ' . ($id > 0 ? 'atualizar' : 'adicionar') . ' depoimento.');
        }
    } catch (PDOException $e) {
        set_flash_message('danger', 'Erro ao processar sua solicitação.');
        if (DEBUG_MODE) {
            $_SESSION['error_details'] = $e->getMessage();
        }
    }
    
    redirect('/admin-depoimentos.php');
}

// Buscar depoimento para edição
$depoimento_edicao = null;
if (isset($_GET['edit']) && (int)$_GET['edit'] > 0) {
    $id = (int)$_GET['edit'];
    
    try {
        $query = "SELECT * FROM depoimentos WHERE id = :id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $depoimento_edicao = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        set_flash_message('danger', 'Erro ao buscar depoimento para edição.');
        if (DEBUG_MODE) {
            $_SESSION['error_details'] = $e->getMessage();
        }
    }
}

// Buscar todos os depoimentos
$depoimentos = [];
try {
    $query = "SELECT * FROM depoimentos ORDER BY data_criacao DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $depoimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    set_flash_message('danger', 'Erro ao buscar depoimentos.');
    if (DEBUG_MODE) {
        $_SESSION['error_details'] = $e->getMessage();
    }
}

// Incluir cabeçalho
include 'views/admin/includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Gerenciar Depoimentos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="admin-dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Depoimentos</li>
    </ol>
    
    <?php display_flash_message(); ?>
    
    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-comment-dots me-1"></i>
                    <?= $depoimento_edicao ? 'Editar Depoimento' : 'Adicionar Depoimento' ?>
                </div>
                <div class="card-body">
                    <form method="post" action="admin-depoimentos.php">
                        <?php if ($depoimento_edicao): ?>
                            <input type="hidden" name="id" value="<?= $depoimento_edicao['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?= $depoimento_edicao ? htmlspecialchars($depoimento_edicao['nome']) : '' ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cargo" class="form-label">Cargo</label>
                            <input type="text" class="form-control" id="cargo" name="cargo" value="<?= $depoimento_edicao ? htmlspecialchars($depoimento_edicao['cargo'] ?? '') : '' ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="empresa" class="form-label">Empresa</label>
                            <input type="text" class="form-control" id="empresa" name="empresa" value="<?= $depoimento_edicao ? htmlspecialchars($depoimento_edicao['empresa'] ?? '') : '' ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="texto" class="form-label">Depoimento <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="texto" name="texto" rows="4" required><?= $depoimento_edicao ? htmlspecialchars($depoimento_edicao['texto']) : '' ?></textarea>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="aprovado" name="aprovado" <?= ($depoimento_edicao && isset($depoimento_edicao['aprovado']) && $depoimento_edicao['aprovado'] == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="aprovado">Aprovado</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><?= $depoimento_edicao ? 'Atualizar' : 'Adicionar' ?></button>
                        
                        <?php if ($depoimento_edicao): ?>
                            <a href="admin-depoimentos.php" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Lista de Depoimentos
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Cargo/Empresa</th>
                                    <th>Depoimento</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($depoimentos)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Nenhum depoimento encontrado.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($depoimentos as $depoimento): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($depoimento['nome']) ?></td>
                                            <td>
                                                <?php if (!empty($depoimento['cargo'] ?? '')): ?>
                                                    <?= htmlspecialchars($depoimento['cargo']) ?>
                                                    <?= !empty($depoimento['empresa'] ?? '') ? ' - ' . htmlspecialchars($depoimento['empresa']) : '' ?>
                                                <?php else: ?>
                                                    <?= !empty($depoimento['empresa'] ?? '') ? htmlspecialchars($depoimento['empresa']) : '-' ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= truncate(htmlspecialchars($depoimento['texto']), 100) ?></td>
                                            <td>
                                                <?php if (isset($depoimento['aprovado']) && $depoimento['aprovado'] == 1): ?>
                                                    <span class="badge bg-success">Aprovado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">Pendente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($depoimento['data_criacao'])) ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="admin-depoimentos.php?edit=<?= $depoimento['id'] ?>" class="btn btn-sm btn-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <?php if (isset($depoimento['aprovado']) && $depoimento['aprovado'] == 1): ?>
                                                        <a href="admin-depoimentos.php?action=reprovar&id=<?= $depoimento['id'] ?>" class="btn btn-sm btn-warning" title="Reprovar">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="admin-depoimentos.php?action=aprovar&id=<?= $depoimento['id'] ?>" class="btn btn-sm btn-success" title="Aprovar">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <a href="admin-depoimentos.php?action=excluir&id=<?= $depoimento['id'] ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este depoimento?')">
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
        </div>
    </div>
</div>

<?php include 'views/admin/includes/footer.php'; ?>
