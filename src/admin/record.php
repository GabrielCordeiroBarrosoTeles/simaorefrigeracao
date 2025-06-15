<?php
// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/functions.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: admin-login.php');
    exit;
}

// Verificar se a tabela e o ID foram especificados
if (!isset($_GET['table']) || empty($_GET['table']) || !isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: admin-dashboard.php');
    exit;
}

$table = $_GET['table'];
$id = (int)$_GET['id'];
$edit_mode = isset($_GET['edit']) && $_GET['edit'] == 1;

// Conectar ao banco de dados
$db = db_connect();

// Verificar se a tabela existe
try {
    $query = "SHOW TABLES LIKE :table";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':table', $table);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        header('Location: admin-dashboard.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: admin-dashboard.php');
    exit;
}

// Obter colunas da tabela
$columns = [];
$column_types = [];
try {
    $query = "SHOW COLUMNS FROM $table";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $columns_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns_data as $column) {
        $columns[] = $column['Field'];
        $column_types[$column['Field']] = $column['Type'];
    }
} catch (PDOException $e) {
    // Ignorar erro
}

// Obter registro
$record = [];
try {
    $query = "SELECT * FROM $table WHERE id = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        header('Location: admin-table.php?table=' . $table);
        exit;
    }
} catch (PDOException $e) {
    header('Location: admin-table.php?table=' . $table);
    exit;
}

// Processar formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $edit_mode) {
    $data = [];
    
    foreach ($columns as $column) {
        if ($column === 'id') continue; // Não atualizar o ID
        
        // Tratar campos especiais
        if (strpos($column_types[$column], 'datetime') !== false && empty($_POST[$column])) {
            $data[$column] = null;
        } else if ($column === 'senha' && empty($_POST[$column])) {
            // Não atualizar senha se estiver vazio
            continue;
        } else if ($column === 'senha' && !empty($_POST[$column])) {
            // Hash da senha
            $data[$column] = password_hash($_POST[$column], PASSWORD_DEFAULT);
        } else {
            $data[$column] = $_POST[$column] ?? null;
        }
    }
    
    // Atualizar registro
    try {
        $set_parts = [];
        foreach ($data as $column => $value) {
            $set_parts[] = "$column = :$column";
        }
        
        $query = "UPDATE $table SET " . implode(', ', $set_parts) . " WHERE id = :id";
        $stmt = $db->prepare($query);
        
        foreach ($data as $column => $value) {
            $stmt->bindValue(":$column", $value);
        }
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $message = "Registro atualizado com sucesso!";
        $message_type = "success";
        
        // Redirecionar para evitar reenvio do formulário
        header("Location: admin-record.php?table=$table&id=$id&message=$message&type=$message_type");
        exit;
    } catch (PDOException $e) {
        $message = "Erro ao atualizar registro: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Processar logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Painel Administrativo | <?= SITE_NAME ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f6f9;
        }
        .navbar {
            background-color: #1f2937;
            padding: 1rem;
        }
        .navbar-brand {
            color: white;
            font-weight: 700;
            display: flex;
            align-items: center;
        }
        .navbar-brand i {
            margin-right: 10px;
            font-size: 1.5rem;
        }
        .navbar-brand:hover {
            color: #f3f4f6;
        }
        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.8);
        }
        .navbar-nav .nav-link:hover {
            color: white;
        }
        .main-content {
            padding: 2rem;
        }
        .card {
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 15px 20px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-body {
            padding: 20px;
        }
        .btn-view {
            background-color: #2563eb;
            color: white;
        }
        .btn-view:hover {
            background-color: #1d4ed8;
            color: white;
        }
        .btn-edit {
            background-color: #10b981;
            color: white;
        }
        .btn-edit:hover {
            background-color: #059669;
            color: white;
        }
        .btn-delete {
            background-color: #ef4444;
            color: white;
        }
        .btn-delete:hover {
            background-color: #dc2626;
            color: white;
        }
        .alert {
            border-radius: 4px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border-color: #a7f3d0;
        }
        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border-color: #fecaca;
        }
        .form-group label {
            font-weight: 500;
        }
        .form-control {
            border-radius: 4px;
            border: 1px solid #d1d5db;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }
        .btn-save {
            background-color: #10b981;
            color: white;
        }
        .btn-save:hover {
            background-color: #059669;
            color: white;
        }
        .btn-cancel {
            background-color: #6b7280;
            color: white;
        }
        .btn-cancel:hover {
            background-color: #4b5563;
            color: white;
        }
        .record-detail {
            margin-bottom: 1rem;
        }
        .record-detail label {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .record-detail p {
            margin-bottom: 0.5rem;
            padding: 0.5rem;
            background-color: #f9fafb;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
        }
        .record-detail p.text-long {
            white-space: pre-wrap;
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="admin-dashboard.php">
                <i class="fas fa-snowflake"></i>
                Simão Refrigeração
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin-dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin-table.php?table=<?= $table ?>">
                            <i class="fas fa-table"></i> Tabela <?= ucfirst($table) ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php" target="_blank">
                            <i class="fas fa-home"></i> Ver Site
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin-dashboard.php?logout=1">
                            <i class="fas fa-sign-out-alt"></i> Sair
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="main-content">
        <div class="container">
            <?php if (isset($_GET['message']) || isset($message)): ?>
                <div class="alert alert-<?= $_GET['type'] ?? $message_type ?? 'info' ?>">
                    <?= $_GET['message'] ?? $message ?? '' ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <?= $edit_mode ? 'Editar' : 'Visualizar' ?> Registro - <?= ucfirst($table) ?> #<?= $id ?>
                    </h5>
                    <div>
                        <?php if (!$edit_mode): ?>
                        <a href="admin-record.php?table=<?= $table ?>&id=<?= $id ?>&edit=1" class="btn btn-sm btn-edit">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <?php endif; ?>
                        <a href="admin-table.php?table=<?= $table ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($edit_mode): ?>
                    <form method="POST" action="">
                        <?php foreach ($columns as $column): ?>
                        <div class="form-group">
                            <label for="<?= $column ?>"><?= ucfirst($column) ?></label>
                            <?php if ($column === 'id'): ?>
                            <input type="text" class="form-control" id="<?= $column ?>" name="<?= $column ?>" value="<?= htmlspecialchars($record[$column] ?? '') ?>" readonly>
                            <?php elseif (strpos($column_types[$column], 'text') !== false): ?>
                            <textarea class="form-control" id="<?= $column ?>" name="<?= $column ?>" rows="5"><?= htmlspecialchars($record[$column] ?? '') ?></textarea>
                            <?php elseif ($column === 'senha'): ?>
                            <input type="password" class="form-control" id="<?= $column ?>" name="<?= $column ?>" placeholder="Deixe em branco para manter a senha atual">
                            <?php elseif (strpos($column_types[$column], 'enum') !== false): ?>
                            <?php
                            preg_match('/enum$$(.*)$$/', $column_types[$column], $matches);
                            $enum_values = [];
                            if (isset($matches[1])) {
                                $enum_values = str_getcsv($matches[1], ',', "'");
                            }
                            ?>
                            <select class="form-control" id="<?= $column ?>" name="<?= $column ?>">
                                <?php foreach ($enum_values as $value): ?>
                                <option value="<?= $value ?>" <?= ($record[$column] ?? '') === $value ? 'selected' : '' ?>><?= ucfirst($value) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php elseif (strpos($column_types[$column], 'datetime') !== false || strpos($column_types[$column], 'date') !== false): ?>
                            <input type="datetime-local" class="form-control" id="<?= $column ?>" name="<?= $column ?>" value="<?= str_replace(' ', 'T', $record[$column] ?? '') ?>">
                            <?php else: ?>
                            <input type="text" class="form-control" id="<?= $column ?>" name="<?= $column ?>" value="<?= htmlspecialchars($record[$column] ?? '') ?>">
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save"></i> Salvar
                            </button>
                            <a href="admin-record.php?table=<?= $table ?>&id=<?= $id ?>" class="btn btn-cancel ml-2">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="row">
                        <?php foreach ($columns as $column): ?>
                        <div class="col-md-6 record-detail">
                            <label><?= ucfirst($column) ?></label>
                            <?php if (strpos($column_types[$column], 'text') !== false): ?>
                            <p class="text-long"><?= nl2br(htmlspecialchars($record[$column] ?? '')) ?></p>
                            <?php elseif ($column === 'senha'): ?>
                            <p>[Senha protegida]</p>
                            <?php else: ?>
                            <p><?= htmlspecialchars($record[$column] ?? '') ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
