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
   
   redirect('admin-contatos.php');
}

// Processar alteração de status
if ($action === 'status' && $id > 0) {
   $novo_status = isset($_GET['status']) ? $_GET['status'] : '';
   
   if (in_array($novo_status, ['novo', 'lido', 'respondido'])) {
       try {
           $query = "UPDATE contatos SET status = :status WHERE id = :id";
           $stmt = $db->prepare($query);
           $stmt->bindParam(':status', $novo_status);
           $stmt->bindParam(':id', $id);
           
           if ($stmt->execute()) {
               set_flash_message('success', "Status do contato atualizado para '{$novo_status}'!");
           } else {
               set_flash_message('danger', 'Erro ao atualizar status do contato.');
           }
       } catch (PDOException $e) {
           set_flash_message('danger', 'Erro ao processar sua solicitação.');
           if (DEBUG_MODE) {
               $_SESSION['error_details'] = $e->getMessage();
           }
       }
   } else {
       set_flash_message('danger', 'Status inválido.');
   }
   
   redirect('admin-contatos.php');
}

// Visualizar contato específico
$contato = null;
if ($action === 'view' && $id > 0) {
   try {
       $query = "SELECT * FROM contatos WHERE id = :id";
       $stmt = $db->prepare($query);
       $stmt->bindParam(':id', $id);
       $stmt->execute();
       $contato = $stmt->fetch(PDO::FETCH_ASSOC);
       
       if ($contato && $contato['status'] === 'novo') {
           // Atualizar status para 'lido'
           $query = "UPDATE contatos SET status = 'lido' WHERE id = :id";
           $stmt = $db->prepare($query);
           $stmt->bindParam(':id', $id);
           $stmt->execute();
           $contato['status'] = 'lido';
       }
   } catch (PDOException $e) {
       set_flash_message('danger', 'Erro ao buscar detalhes do contato.');
       if (DEBUG_MODE) {
           $_SESSION['error_details'] = $e->getMessage();
       }
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

// Título da página
$page_title = 'Contatos';

// Incluir o cabeçalho
include 'views/admin/includes/header.php';
?>

<!-- Sidebar -->
<?php include 'views/admin/includes/sidebar.php'; ?>

<div class="container-fluid">
   <div class="d-sm-flex align-items-center justify-content-between mb-4">
       <h1 class="h3 mb-0 text-gray-800">Contatos</h1>
   </div>
   
   <?php display_flash_message(); ?>
   
   <?php if ($contato): ?>
       <!-- Detalhes do Contato -->
       <div class="card shadow mb-4">
           <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
               <h6 class="m-0 font-weight-bold text-primary">Detalhes do Contato</h6>
               <a href="admin-contatos.php" class="btn btn-sm btn-secondary">
                   <i class="fas fa-arrow-left fa-sm"></i> Voltar
               </a>
           </div>
           <div class="card-body">
               <div class="row">
                   <div class="col-md-6">
                       <p><strong>Nome:</strong> <?= htmlspecialchars($contato['nome']) ?></p>
                       <p><strong>Email:</strong> <?= htmlspecialchars($contato['email']) ?></p>
                       <p><strong>Telefone:</strong> <?= htmlspecialchars($contato['telefone'] ?? 'Não informado') ?></p>
                       <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($contato['data_criacao'])) ?></p>
                   </div>
                   <div class="col-md-6">
                       <p><strong>Status:</strong> 
                           <?php
                           $status_class = 'secondary';
                           $status_text = 'Desconhecido';
                           
                           switch ($contato['status']) {
                               case 'novo':
                                   $status_class = 'danger';
                                   $status_text = 'Novo';
                                   break;
                               case 'lido':
                                   $status_class = 'warning';
                                   $status_text = 'Lido';
                                   break;
                               case 'respondido':
                                   $status_class = 'success';
                                   $status_text = 'Respondido';
                                   break;
                           }
                           ?>
                           <span class="badge badge-<?= $status_class ?>"><?= $status_text ?></span>
                       </p>
                       <div class="btn-group">
                           <a href="admin-contatos.php?action=status&id=<?= $contato['id'] ?>&status=novo" class="btn btn-sm btn-danger">Marcar como Novo</a>
                           <a href="admin-contatos.php?action=status&id=<?= $contato['id'] ?>&status=lido" class="btn btn-sm btn-warning">Marcar como Lido</a>
                           <a href="admin-contatos.php?action=status&id=<?= $contato['id'] ?>&status=respondido" class="btn btn-sm btn-success">Marcar como Respondido</a>
                       </div>
                   </div>
               </div>
               <hr>
               <div class="row">
                   <div class="col-12">
                       <h5>Mensagem:</h5>
                       <div class="p-3 bg-light rounded">
                           <?= nl2br(htmlspecialchars($contato['mensagem'])) ?>
                       </div>
                   </div>
               </div>
               <hr>
               <div class="row">
                   <div class="col-12 text-right">
                       <a href="mailto:<?= htmlspecialchars($contato['email']) ?>" class="btn btn-primary">
                           <i class="fas fa-reply"></i> Responder por Email
                       </a>
                       <a href="admin-contatos.php?action=delete&id=<?= $contato['id'] ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este contato?');">
                           <i class="fas fa-trash"></i> Excluir
                       </a>
                   </div>
               </div>
           </div>
       </div>
   <?php else: ?>
       <!-- Lista de Contatos -->
       <div class="card shadow mb-4">
           <div class="card-header py-3">
               <h6 class="m-0 font-weight-bold text-primary">Todos os Contatos</h6>
           </div>
           <div class="card-body">
               <div class="table-responsive">
                   <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                       <thead>
                           <tr>
                               <th>Nome</th>
                               <th>Email</th>
                               <th>Telefone</th>
                               <th>Data</th>
                               <th>Status</th>
                               <th>Ações</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php if (!empty($contatos)): ?>
                               <?php foreach ($contatos as $contato): ?>
                                   <tr>
                                       <td><?= htmlspecialchars($contato['nome']) ?></td>
                                       <td><?= htmlspecialchars($contato['email']) ?></td>
                                       <td><?= htmlspecialchars($contato['telefone'] ?? 'N/A') ?></td>
                                       <td><?= date('d/m/Y H:i', strtotime($contato['data_criacao'])) ?></td>
                                       <td>
                                           <?php
                                           $status_class = 'secondary';
                                           $status_text = 'Desconhecido';
                                           
                                           switch ($contato['status']) {
                                               case 'novo':
                                                   $status_class = 'danger';
                                                   $status_text = 'Novo';
                                                   break;
                                               case 'lido':
                                                   $status_class = 'warning';
                                                   $status_text = 'Lido';
                                                   break;
                                               case 'respondido':
                                                   $status_class = 'success';
                                                   $status_text = 'Respondido';
                                                   break;
                                           }
                                           ?>
                                           <span class="badge badge-<?= $status_class ?>"><?= $status_text ?></span>
                                       </td>
                                       <td>
                                           <a href="admin-contatos.php?action=view&id=<?= $contato['id'] ?>" class="btn btn-sm btn-info">
                                               <i class="fas fa-eye"></i>
                                           </a>
                                           <a href="admin-contatos.php?action=delete&id=<?= $contato['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este contato?');">
                                               <i class="fas fa-trash"></i>
                                           </a>
                                       </td>
                                   </tr>
                               <?php endforeach; ?>
                           <?php else: ?>
                               <tr>
                                   <td colspan="6" class="text-center">Nenhum contato encontrado.</td>
                               </tr>
                           <?php endif; ?>
                       </tbody>
                   </table>
               </div>
           </div>
       </div>
   <?php endif; ?>
</div>

<?php include 'views/admin/includes/footer.php'; ?>
