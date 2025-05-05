<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    </div>
    
    <?php 
    // Inicializar a variável $stats para evitar os erros
    $stats = [
        'clientes' => 0,
        'agendamentos' => 0,
        'tecnicos' => 0,
        'servicos' => 0
    ];
    
    // Obter estatísticas do banco de dados
    try {
        $db = db_connect();
        
        // Contar clientes
        $query = "SELECT COUNT(*) as total FROM clientes";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['clientes'] = $result['total'] ?? 0;
        
        // Contar agendamentos
        $query = "SELECT COUNT(*) as total FROM agendamentos";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['agendamentos'] = $result['total'] ?? 0;
        
        // Contar técnicos
        $query = "SELECT COUNT(*) as total FROM tecnicos";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['tecnicos'] = $result['total'] ?? 0;
        
        // Contar serviços
        $query = "SELECT COUNT(*) as total FROM servicos";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['servicos'] = $result['total'] ?? 0;
    } catch (PDOException $e) {
        // Em caso de erro, manter os valores padrão
    }
    
    // Verificar se a função display_flash_message existe
    if (function_exists('display_flash_message')) {
        display_flash_message();
    }
    ?>
    
    <!-- Stats Cards -->
    <div class="row g-4">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Clientes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['clientes'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <a href="admin-table.php?table=clientes" class="card-footer bg-transparent text-primary small">
                    Ver todos <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Agendamentos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['agendamentos'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <a href="admin-table.php?table=agendamentos" class="card-footer bg-transparent text-success small">
                    Ver todos <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Técnicos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['tecnicos'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-hard-hat fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <a href="admin-table.php?table=tecnicos" class="card-footer bg-transparent text-info small">
                    Ver todos <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Serviços</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['servicos'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tools fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <a href="admin-table.php?table=servicos" class="card-footer bg-transparent text-warning small">
                    Ver todos <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Agendamentos Recentes -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        Agendamentos Recentes
                    </h6>
                    <a href="admin-table.php?table=agendamentos" class="btn btn-sm btn-primary">
                        Ver Todos
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Serviço</th>
                                    <th class="d-none d-md-table-cell">Data</th>
                                    <th class="d-none d-md-table-cell">Hora</th>
                                    <th>Status</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Inicializar a variável $agendamentos_recentes
                                $agendamentos_recentes = [];
                                
                                // Obter agendamentos recentes
                                try {
                                    $query = "SELECT a.*, c.nome as cliente_nome, s.nome as servico_nome 
                                             FROM agendamentos a
                                             LEFT JOIN clientes c ON a.cliente_id = c.id
                                             LEFT JOIN servicos s ON a.servico_id = s.id
                                             ORDER BY a.data_agendamento DESC, a.hora_inicio DESC
                                             LIMIT 5";
                                    $stmt = $db->prepare($query);
                                    $stmt->execute();
                                    $agendamentos_recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                } catch (PDOException $e) {
                                    // Em caso de erro, manter o array vazio
                                }
                                
                                if (empty($agendamentos_recentes)): 
                                ?>
                                <tr>
                                    <td colspan="6" class="text-center">Nenhum agendamento encontrado.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($agendamentos_recentes as $agendamento): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($agendamento['cliente_nome']) ?></td>
                                        <td><?= htmlspecialchars($agendamento['servico_nome']) ?></td>
                                        <td class="d-none d-md-table-cell"><?= isset($agendamento['data_agendamento']) ? date('d/m/Y', strtotime($agendamento['data_agendamento'])) : 'N/A' ?></td>
                                        <td class="d-none d-md-table-cell"><?= isset($agendamento['hora_inicio']) ? date('H:i', strtotime($agendamento['hora_inicio'])) : 'N/A' ?></td>
                                        <td>
                                            <?php
                                            $status_class = 'badge-info';
                                            $status_text = 'Em andamento';
                                            
                                            if (isset($agendamento['status'])) {
                                                switch ($agendamento['status']) {
                                                    case 'pendente':
                                                        $status_class = 'badge-warning';
                                                        $status_text = 'Pendente';
                                                        break;
                                                    case 'concluido':
                                                        $status_class = 'badge-success';
                                                        $status_text = 'Concluído';
                                                        break;
                                                    case 'cancelado':
                                                        $status_class = 'badge-danger';
                                                        $status_text = 'Cancelado';
                                                        break;
                                                }
                                            }
                                            ?>
                                            <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                                        </td>
                                        <td class="text-center">
                                            <a href="admin-form.php?form=agendamento&id=<?= $agendamento['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
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
        
        <!-- Contatos Recentes -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-envelope mr-1"></i>
                        Contatos Recentes
                    </h6>
                    <a href="admin-contatos.php" class="btn btn-sm btn-primary">
                        Ver Todos
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php 
                    // Inicializar a variável $contatos_recentes
                    $contatos_recentes = [];
                    
                    // Obter contatos recentes
                    try {
                        $query = "SELECT * FROM contatos ORDER BY data_criacao DESC LIMIT 3";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $contatos_recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        // Em caso de erro, manter o array vazio
                    }
                    
                    if (empty($contatos_recentes)): 
                    ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Nenhum contato recente.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($contatos_recentes as $contato): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($contato['nome'] ?? 'Nome não disponível') ?></h6>
                                        <small class="text-muted">
                                            <?= isset($contato['data_criacao']) ? date('d/m/Y', strtotime($contato['data_criacao'])) : 'Data não disponível' ?>
                                        </small>
                                    </div>
                                    <p class="mb-1 text-truncate"><?= htmlspecialchars($contato['mensagem'] ?? 'Mensagem não disponível') ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted"><?= htmlspecialchars($contato['email'] ?? 'Email não disponível') ?></small>
                                        <a href="admin-contatos.php?action=view&id=<?= $contato['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
