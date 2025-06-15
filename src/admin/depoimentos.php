<?php
require_once 'bootstrap.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
  redirect('/admin-login.php');
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
  
  redirect('/admin-depoimentos.php');
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
  
  redirect('/admin-depoimentos.php');
}

// Buscar todos os depoimentos
$depoimentos = [];
try {
  $query = "SELECT * FROM depoimentos ORDER BY data_criacao DESC";
  $stmt = $db->prepare($query);
  $stmt->execute();
  $depoimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  set_flash_message('danger', 'Erro ao buscar depoimentos: ' . $e->getMessage());
  if (DEBUG_MODE) {
      $_SESSION['error_details'] = $e->getMessage();
  }
}

// Título da página
$page_title = 'Depoimentos';

// Incluir o cabeçalho
include 'views/admin/includes/header.php';
?>

<!-- Sidebar -->
<?php include 'views/admin/includes/sidebar.php'; ?>

<div class="container-fluid">
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
      <h1 class="h3 mb-0 text-gray-800">Depoimentos</h1>
      <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addDepoimentoModal">
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
                          <th>Nome</th>
                          <th>Tipo</th>
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
                                  <td><?= htmlspecialchars($depoimento['nome']) ?></td>
                                  <td><?= htmlspecialchars($depoimento['tipo']) ?></td>
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
                                      <a href="#" class="btn btn-sm btn-info edit-depoimento" 
                                         data-id="<?= $depoimento['id'] ?>"
                                         data-nome="<?= htmlspecialchars($depoimento['nome']) ?>"
                                         data-tipo="<?= htmlspecialchars($depoimento['tipo']) ?>"
                                         data-texto="<?= htmlspecialchars($depoimento['texto']) ?>"
                                         data-foto="<?= htmlspecialchars($depoimento['foto'] ?? '') ?>"
                                         data-toggle="modal" data-target="#editDepoimentoModal">
                                          <i class="fas fa-edit"></i>
                                      </a>
                                      <a href="/admin-depoimentos.php?action=toggle&id=<?= $depoimento['id'] ?>" class="btn btn-sm <?= $depoimento['ativo'] ? 'btn-secondary' : 'btn-success' ?>">
                                          <i class="fas <?= $depoimento['ativo'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                      </a>
                                      <a href="/admin-depoimentos.php?action=delete&id=<?= $depoimento['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este depoimento?');">
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

<!-- Modal Adicionar Depoimento -->
<div class="modal fade" id="addDepoimentoModal" tabindex="-1" role="dialog" aria-labelledby="addDepoimentoModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="addDepoimentoModalLabel">Adicionar Depoimento</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <form action="admin-save.php" method="post" enctype="multipart/form-data">
              <div class="modal-body">
                  <input type="hidden" name="form" value="depoimento">
                  <input type="hidden" name="action" value="create">
                  
                  <div class="form-group">
                      <label for="nome">Nome</label>
                      <input type="text" class="form-control" id="nome" name="nome" required>
                  </div>
                  
                  <div class="form-group">
                      <label for="tipo">Tipo</label>
                      <select class="form-control" id="tipo" name="tipo" required>
                          <option value="Cliente">Cliente</option>
                          <option value="Parceiro">Parceiro</option>
                          <option value="Fornecedor">Fornecedor</option>
                      </select>
                  </div>
                  
                  <div class="form-group">
                      <label for="texto">Depoimento</label>
                      <textarea class="form-control" id="texto" name="texto" rows="4" required></textarea>
                  </div>
                  
                  <div class="form-group">
                      <label for="foto">Foto (opcional)</label>
                      <input type="file" class="form-control-file" id="foto" name="foto">
                  </div>
                  
                  <div class="form-group">
                      <div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input" id="ativo" name="ativo" value="1" checked>
                          <label class="custom-control-label" for="ativo">Ativo</label>
                      </div>
                  </div>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-primary">Salvar</button>
              </div>
          </form>
      </div>
  </div>
</div>

<!-- Modal Editar Depoimento -->
<div class="modal fade" id="editDepoimentoModal" tabindex="-1" role="dialog" aria-labelledby="editDepoimentoModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="editDepoimentoModalLabel">Editar Depoimento</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <form action="admin-save.php" method="post" enctype="multipart/form-data">
              <div class="modal-body">
                  <input type="hidden" name="form" value="depoimento">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="id" id="edit_id">
                  
                  <div class="form-group">
                      <label for="edit_nome">Nome</label>
                      <input type="text" class="form-control" id="edit_nome" name="nome" required>
                  </div>
                  
                  <div class="form-group">
                      <label for="edit_tipo">Tipo</label>
                      <select class="form-control" id="edit_tipo" name="tipo" required>
                          <option value="Cliente">Cliente</option>
                          <option value="Parceiro">Parceiro</option>
                          <option value="Fornecedor">Fornecedor</option>
                      </select>
                  </div>
                  
                  <div class="form-group">
                      <label for="edit_texto">Depoimento</label>
                      <textarea class="form-control" id="edit_texto" name="texto" rows="4" required></textarea>
                  </div>
                  
                  <div class="form-group">
                      <label for="edit_foto">Foto (opcional)</label>
                      <input type="file" class="form-control-file" id="edit_foto" name="foto">
                      <small class="form-text text-muted">Deixe em branco para manter a foto atual.</small>
                      <div id="foto_preview" class="mt-2"></div>
                  </div>
                  
                  <div class="form-group">
                      <div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input" id="edit_ativo" name="ativo" value="1">
                          <label class="custom-control-label" for="edit_ativo">Ativo</label>
                      </div>
                  </div>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-primary">Salvar</button>
              </div>
          </form>
      </div>
  </div>
</div>

<script>
$(document).ready(function() {
  // Inicializar DataTable
  $('#dataTable').DataTable({
      language: {
          url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
      }
  });
  
  // Preencher modal de edição
  $('.edit-depoimento').click(function() {
      var id = $(this).data('id');
      var nome = $(this).data('nome');
      var tipo = $(this).data('tipo');
      var texto = $(this).data('texto');
      var foto = $(this).data('foto');
      var ativo = $(this).closest('tr').find('.badge-success').length > 0;
      
      $('#edit_id').val(id);
      $('#edit_nome').val(nome);
      $('#edit_tipo').val(tipo);
      $('#edit_texto').val(texto);
      $('#edit_ativo').prop('checked', ativo);
      
      // Mostrar preview da foto se existir
      if (foto) {
          $('#foto_preview').html('<img src="/uploads/' + foto + '" class="img-thumbnail" style="max-height: 100px;">');
      } else {
          $('#foto_preview').empty();
      }
  });
});
</script>

<?php include 'views/admin/includes/footer.php'; ?>
