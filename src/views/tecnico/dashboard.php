<?php require 'views/admin/includes/header.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dashboard do Técnico</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active">Dashboard</li>
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
        
        <!-- Informações do Técnico -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bem-vindo, <?= $tecnico['nome'] ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 text-center">
                                <img class="img-circle elevation-2" src="https://ui-avatars.com/api/?name=<?= urlencode($tecnico['nome']) ?>&background=<?= urlencode(str_replace('#', '', $tecnico['cor'])) ?>&color=fff&size=128" alt="Foto do Técnico" style="width: 100px; height: 100px;">
                            </div>
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-md-4">
                                        <p><strong>Nome:</strong> <?= $tecnico['nome'] ?></p>
                                        <p><strong>Email:</strong> <?= $tecnico['email'] ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Telefone:</strong> <?= $tecnico['telefone'] ?></p>
                                        <p><strong>Especialidade:</strong> <?= $tecnico['especialidade'] ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Status:</strong> 
                                            <?php if ($tecnico['status'] == 'ativo'): ?>
                                                <span class="badge badge-success">Ativo</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Inativo</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estatísticas -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="total-agendamentos">...</h3>
                        <p>Total de Agendamentos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <a href="/tecnico/calendario" class="small-box-footer">
                        Ver todos <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="total-concluidos">...</h3>
                        <p>Agendamentos Concluídos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="/tecnico/calendario" class="small-box-footer">
                        Ver todos <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="total-pendentes">...</h3>
                        <p>Agendamentos Pendentes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <a href="/tecnico/calendario" class="small-box-footer">
                        Ver todos <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="total-hoje"><?= count($agendamentos_hoje) ?></h3>
                        <p>Agendamentos Hoje</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <a href="#agendamentos-hoje" class="small-box-footer">
                        Ver abaixo <i class="fas fa-arrow-circle-down"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Agendamentos de Hoje -->
        <div class="row">
            <div class="col-md-12">
                <div class="card" id="agendamentos-hoje">
                    <div class="card-header">
                        <h3 class="card-title">Agendamentos de Hoje (<?= date('d/m/Y') ?>)</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($agendamentos_hoje)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i> Você não tem agendamentos para hoje.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Horário</th>
                                            <th>Cliente</th>
                                            <th>Serviço</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($agendamentos_hoje as $agendamento): ?>
                                            <tr>
                                                <td>
                                                    <?= substr($agendamento['hora_inicio'], 0, 5) ?>
                                                    <?php if (!empty($agendamento['hora_fim'])): ?>
                                                        - <?= substr($agendamento['hora_fim'], 0, 5) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?= $agendamento['cliente_nome'] ?></strong><br>
                                                    <small><?= $agendamento['cliente_telefone'] ?></small>
                                                </td>
                                                <td><?= $agendamento['servico_nome'] ?></td>
                                                <td>
                                                    <?php if ($agendamento['status'] == 'pendente'): ?>
                                                        <span class="badge badge-warning">Pendente</span>
                                                    <?php elseif ($agendamento['status'] == 'concluido'): ?>
                                                        <span class="badge badge-success">Concluído</span>
                                                    <?php elseif ($agendamento['status'] == 'cancelado'): ?>
                                                        <span class="badge badge-danger">Cancelado</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="/tecnico/agendamento?id=<?= $agendamento['id'] ?>" class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i> Detalhes
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Próximos Agendamentos -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Próximos Agendamentos Pendentes</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($agendamentos_pendentes)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i> Você não tem agendamentos pendentes.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Horário</th>
                                            <th>Cliente</th>
                                            <th>Serviço</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $count = 0;
                                        foreach ($agendamentos_pendentes as $agendamento): 
                                            if ($count >= 5) break; // Limitar a 5 agendamentos
                                            $count++;
                                        ?>
                                            <tr>
                                                <td><?= format_date($agendamento['data_agendamento'], 'd/m/Y') ?></td>
                                                <td>
                                                    <?= substr($agendamento['hora_inicio'], 0, 5) ?>
                                                    <?php if (!empty($agendamento['hora_fim'])): ?>
                                                        - <?= substr($agendamento['hora_fim'], 0, 5) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?= $agendamento['cliente_nome'] ?></strong><br>
                                                    <small><?= $agendamento['cliente_telefone'] ?></small>
                                                </td>
                                                <td><?= $agendamento['servico_nome'] ?></td>
                                                <td>
                                                    <a href="/tecnico/agendamento?id=<?= $agendamento['id'] ?>" class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i> Detalhes
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                
                                <?php if (count($agendamentos_pendentes) > 5): ?>
                                    <div class="text-center mt-3">
                                        <a href="/tecnico/calendario" class="btn btn-primary">
                                            <i class="fas fa-calendar-alt"></i> Ver todos os agendamentos
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Carregar estatísticas
    fetch('/admin/tecnicos/api?action=stats&id=<?= $tecnico['id'] ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('total-agendamentos').textContent = data.total || 0;
                document.getElementById('total-concluidos').textContent = data.concluidos || 0;
                document.getElementById('total-pendentes').textContent = data.pendentes || 0;
            } else {
                document.getElementById('total-agendamentos').textContent = '0';
                document.getElementById('total-concluidos').textContent = '0';
                document.getElementById('total-pendentes').textContent = '0';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar estatísticas:', error);
            document.getElementById('total-agendamentos').textContent = 'Erro';
            document.getElementById('total-concluidos').textContent = 'Erro';
            document.getElementById('total-pendentes').textContent = 'Erro';
        });
});
</script>

<?php require 'views/admin/includes/footer.php'; ?>
