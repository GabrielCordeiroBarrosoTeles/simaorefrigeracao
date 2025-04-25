<?php require 'views/admin/includes/header.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Gerenciar Clientes</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                    <li class="breadcrumb-item active">Clientes</li>
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
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lista de Clientes</h3>
                <div class="card-tools">
                    <a href="/admin/clientes/novo" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Novo Cliente
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 50px">ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Tipo</th>
                                <th style="width: 150px">Data de Criação</th>
                                <th style="width: 120px">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clientes)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Nenhum cliente encontrado.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($clientes as $cliente): ?>
                                <tr>
                                    <td><?= $cliente['id'] ?></td>
                                    <td><?= $cliente['nome'] ?></td>
                                    <td><?= $cliente['email'] ?></td>
                                    <td><?= $cliente['telefone'] ?></td>
                                    <td>
                                        <?php if ($cliente['tipo'] == 'residencial'): ?>
                                            <span class="badge badge-info">Residencial</span>
                                        <?php elseif ($cliente['tipo'] == 'comercial'): ?>
                                            <span class="badge badge-primary">Comercial</span>
                                        <?php elseif ($cliente['tipo'] == 'industrial'): ?>
                                            <span class="badge badge-warning">Industrial</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= format_date($cliente['data_criacao'], 'd/m/Y') ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/admin/clientes/agendamentos?id=<?= $cliente['id'] ?>" class="btn btn-info btn-sm" title="Agendamentos">
                                                <i class="fas fa-calendar"></i>
                                            </a>
                                            <a href="/admin/clientes/editar?id=<?= $cliente['id'] ?>" class="btn btn-primary btn-sm" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="/admin/clientes/excluir?id=<?= $cliente['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este cliente?')" title="Excluir">
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
</section>

<?php require 'views/admin/includes/footer.php'; ?>
