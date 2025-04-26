<?php require_once 'views/admin/includes/header.php'; ?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Agendamentos</h1>
        <a href="/simaorefrigeracao/admin/agendamentos/novo" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Novo Agendamento
        </a>
    </div>

    <?php display_flash_message(); ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Agendamentos</h6>
            <div class="dropdown no-arrow">
                <a href="/simaorefrigeracao/admin/agendamentos/calendario" class="btn btn-sm btn-info">
                    <i class="fas fa-calendar fa-sm"></i> Ver Calendário
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Cliente</th>
                            <th>Serviço</th>
                            <th>Técnico</th>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($agendamentos) && !empty($agendamentos)): ?>
                            <?php foreach ($agendamentos as $agendamento): ?>
                                <tr>
                                    <td><?php echo $agendamento['id']; ?></td>
                                    <td><?php echo $agendamento['titulo']; ?></td>
                                    <td><?php echo $agendamento['cliente_nome']; ?></td>
                                    <td><?php echo $agendamento['servico_titulo']; ?></td>
                                    <td><?php echo $agendamento['tecnico_nome'] ?? 'Não atribuído'; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($agendamento['hora_inicio'])); ?> - <?php echo date('H:i', strtotime($agendamento['hora_fim'])); ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch ($agendamento['status']) {
                                            case 'pendente':
                                                $status_class = 'warning';
                                                break;
                                            case 'confirmado':
                                                $status_class = 'primary';
                                                break;
                                            case 'concluido':
                                                $status_class = 'success';
                                                break;
                                            case 'cancelado':
                                                $status_class = 'danger';
                                                break;
                                        }
                                        ?>
                                        <span class="badge badge-<?php echo $status_class; ?>"><?php echo ucfirst($agendamento['status']); ?></span>
                                    </td>
                                    <td>
                                        <a href="/simaorefrigeracao/admin/agendamentos/editar?id=<?php echo $agendamento['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal" data-id="<?php echo $agendamento['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">Nenhum agendamento encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Tem certeza que deseja excluir este agendamento?</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <a class="btn btn-danger" id="confirmDelete" href="#">Excluir</a>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#deleteModal').on('show.bs.modal', function(e) {
            var id = $(e.relatedTarget).data('id');
            $('#confirmDelete').attr('href', '/simaorefrigeracao/admin/agendamentos/excluir?id=' + id);
        });
    });
</script>

<?php require_once 'views/admin/includes/footer.php'; ?>
