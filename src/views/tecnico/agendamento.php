<?php require 'views/admin/includes/header.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Detalhes do Agendamento</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/tecnico">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/tecnico/calendario">Calendário</a></li>
                    <li class="breadcrumb-item active">Detalhes</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php
        // Exibir mensagem flash
        $flash_message = get_flash_message();
        if ($flash_message) {
            echo '<div class="alert alert-' . $flash_message['type'] . '">' . $flash_message['message'] . '</div>';
        }
        ?>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?= $agendamento['titulo'] ?></h3>
                        <div class="card-tools">
                            <a href="/tecnico/calendario" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Voltar para o Calendário
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary">
                                        <h3 class="card-title">Informações do Agendamento</h3>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Serviço:</strong> <?= $agendamento['servico_nome'] ?></p>
                                        <p><strong>Data:</strong> <?= format_date($agendamento['data_agendamento'], 'd/m/Y') ?></p>
                                        <p><strong>Horário:</strong> 
                                            <?= substr($agendamento['hora_inicio'], 0, 5) ?>
                                            <?php if (!empty($agendamento['hora_fim'])): ?>
                                                - <?= substr($agendamento['hora_fim'], 0, 5) ?>
                                            <?php endif; ?>
                                        </p>
                                        <p><strong>Status:</strong> 
                                            <?php if ($agendamento['status'] == 'pendente'): ?>
                                                <span class="badge badge-warning">Pendente</span>
                                            <?php elseif ($agendamento['status'] == 'concluido'): ?>
                                                <span class="badge badge-success">Concluído</span>
                                            <?php elseif ($agendamento['status'] == 'cancelado'): ?>
                                                <span class="badge badge-danger">Cancelado</span>
                                            <?php endif; ?>
                                        </p>
                                        <p><strong>Observações:</strong></p>
                                        <div class="p-3 bg-light rounded">
                                            <?= nl2br($agendamento['observacoes'] ?? 'Nenhuma observação registrada.') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-info">
                                        <h3 class="card-title">Informações do Cliente</h3>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Nome:</strong> <?= $agendamento['cliente_nome'] ?></p>
                                        <p><strong>Telefone:</strong> <?= $agendamento['cliente_telefone'] ?></p>
                                        <p><strong>Email:</strong> <?= $agendamento['cliente_email'] ?></p>
                                        <p><strong>Endereço:</strong> <?= $agendamento['cliente_endereco'] ?></p>
                                        <p><strong>Cidade/Estado:</strong> <?= $agendamento['cliente_cidade'] ?>/<?= $agendamento['cliente_estado'] ?></p>
                                        <p><strong>Tipo:</strong> 
                                            <?php if ($agendamento['cliente_tipo'] == 'residencial'): ?>
                                                <span class="badge badge-primary">Residencial</span>
                                            <?php elseif ($agendamento['cliente_tipo'] == 'comercial'): ?>
                                                <span class="badge badge-success">Comercial</span>
                                            <?php elseif ($agendamento['cliente_tipo'] == 'industrial'): ?>
                                                <span class="badge badge-warning">Industrial</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Formulário para atualizar status -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-success">
                                        <h3 class="card-title">Atualizar Status do Agendamento</h3>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="/tecnico/atualizar-status">
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <input type="hidden" name="id" value="<?= $agendamento['id'] ?>">
                                            
                                            <div class="form-group">
                                                <label for="status">Status:</label>
                                                <select name="status" id="status" class="form-control">
                                                    <option value="pendente" <?= $agendamento['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                                    <option value="concluido" <?= $agendamento['status'] == 'concluido' ? 'selected' : '' ?>>Concluído</option>
                                                    <option value="cancelado" <?= $agendamento['status'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                                </select>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="observacoes">Observações:</label>
                                                <textarea name="observacoes" id="observacoes" class="form-control" rows="5"><?= $agendamento['observacoes'] ?></textarea>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save"></i> Salvar Alterações
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require 'views/admin/includes/footer.php'; ?>
