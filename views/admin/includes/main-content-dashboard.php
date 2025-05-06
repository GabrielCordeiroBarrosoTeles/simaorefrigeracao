<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" id="printReport">
            <i class="fas fa-download fa-sm text-white-50"></i> Gerar Relatório
        </a>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Clientes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['clientes'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Agendamentos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['agendamentos'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Técnicos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['tecnicos'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-hard-hat fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Agendamentos Hoje</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['agendamentos_hoje'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="row">
        <!-- Agendamentos Recentes -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Agendamentos Recentes</h6>
                    <a href="admin-table.php?table=agendamentos" class="btn btn-sm btn-primary">
                        <i class="fas fa-calendar-check fa-sm"></i> Ver Todos
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Serviço</th>
                                    <th>Data</th>
                                    <th>Hora</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($agendamentos_recentes)): ?>
                                    <?php foreach ($agendamentos_recentes as $agendamento): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($agendamento['cliente_nome'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($agendamento['servico_nome'] ?? 'N/A') ?></td>
                                            <td><?= date('d/m/Y', strtotime($agendamento['data_agendamento'])) ?></td>
                                            <td><?= htmlspecialchars($agendamento['hora_inicio']) ?></td>
                                            <td>
                                                <?php
                                                $status_class = 'secondary';
                                                $status_text = 'Desconhecido';
                                                
                                                switch ($agendamento['status']) {
                                                    case 'pendente':
                                                        $status_class = 'warning';
                                                        $status_text = 'Pendente';
                                                        break;
                                                    case 'confirmado':
                                                        $status_class = 'primary';
                                                        $status_text = 'Confirmado';
                                                        break;
                                                    case 'concluido':
                                                        $status_class = 'success';
                                                        $status_text = 'Concluído';
                                                        break;
                                                    case 'cancelado':
                                                        $status_class = 'danger';
                                                        $status_text = 'Cancelado';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge badge-<?= $status_class ?>"><?= $status_text ?></span>
                                            </td>
                                            <td>
                                                <a href="admin-form.php?table=agendamentos&action=edit&id=<?= $agendamento['id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Nenhum agendamento encontrado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Próximos Agendamentos -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Próximos Agendamentos</h6>
                    <a href="admin-calendario.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-calendar-alt fa-sm"></i> Ver Calendário
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Serviço</th>
                                    <th>Data</th>
                                    <th>Hora</th>
                                    <th>Técnico</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($proximos_agendamentos)): ?>
                                    <?php foreach ($proximos_agendamentos as $agendamento): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($agendamento['cliente_nome'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($agendamento['servico_nome'] ?? 'N/A') ?></td>
                                            <td><?= date('d/m/Y', strtotime($agendamento['data_agendamento'])) ?></td>
                                            <td><?= htmlspecialchars($agendamento['hora_inicio']) ?></td>
                                            <td><?= htmlspecialchars($agendamento['tecnico_nome'] ?? 'N/A') ?></td>
                                            <td>
                                                <a href="admin-form.php?table=agendamentos&action=edit&id=<?= $agendamento['id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Nenhum agendamento encontrado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terceira Linha -->
    <div class="row">
        <!-- Contatos Recentes -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Contatos Recentes</h6>
                    <a href="admin-contatos.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-envelope fa-sm"></i> Ver Todos
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($contatos_recentes)): ?>
                                    <?php foreach ($contatos_recentes as $contato): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($contato['nome']) ?></td>
                                            <td><?= htmlspecialchars($contato['email']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($contato['data_criacao'])) ?></td>
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
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Nenhum contato encontrado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clientes Recentes -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Clientes Recentes</h6>
                    <a href="admin-table.php?table=clientes" class="btn btn-sm btn-primary">
                        <i class="fas fa-users fa-sm"></i> Ver Todos
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Telefone</th>
                                    <th>Data Cadastro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($clientes_recentes)): ?>
                                    <?php foreach ($clientes_recentes as $cliente): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($cliente['nome']) ?></td>
                                            <td><?= htmlspecialchars($cliente['email'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($cliente['telefone'] ?? 'N/A') ?></td>
                                            <td><?= date('d/m/Y', strtotime($cliente['data_criacao'])) ?></td>
                                            <td>
                                                <a href="admin-form.php?table=clientes&action=edit&id=<?= $cliente['id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Nenhum cliente encontrado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
