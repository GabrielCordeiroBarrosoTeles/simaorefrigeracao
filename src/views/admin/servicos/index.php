<?php require 'views/admin/includes/header.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Gerenciar Serviços</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                    <li class="breadcrumb-item active">Serviços</li>
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
                <h3 class="card-title">Lista de Serviços</h3>
                <div class="card-tools">
                    <a href="/admin/servicos/novo" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Novo Serviço
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 50px">ID</th>
                            <th>Título</th>
                            <th>Ícone</th>
                            <th>Descrição</th>
                            <th style="width: 150px">Data de Criação</th>
                            <th style="width: 120px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($servicos)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Nenhum serviço encontrado.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($servicos as $servico): ?>
                            <tr>
                                <td><?= $servico['id'] ?></td>
                                <td><?= $servico['titulo'] ?></td>
                                <td><i class="fas fa-<?= $servico['icone'] ?>"></i> <?= $servico['icone'] ?></td>
                                <td><?= truncate($servico['descricao'], 100) ?></td>
                                <td><?= format_date($servico['data_criacao'], 'd/m/Y') ?></td>
                                <td>
                                    <a href="/admin/servicos/editar?id=<?= $servico['id'] ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/admin/servicos/excluir?id=<?= $servico['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este serviço?')">
                                        <i class="fas fa-trash"></i>
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
</section>

<?php require 'views/admin/includes/footer.php'; ?>
