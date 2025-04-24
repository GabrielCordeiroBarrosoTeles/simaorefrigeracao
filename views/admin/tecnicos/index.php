<?php require 'views/admin/includes/header.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Gerenciar Técnicos</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                    <li class="breadcrumb-item active">Técnicos</li>
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
                <h3 class="card-title">Lista de Técnicos</h3>
                <div class="card-tools">
                    <a href="/admin/tecnicos/novo" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Novo Técnico
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 50px">ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Especialidade</th>
                            <th>Status</th>
                            <th style="width: 150px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tecnicos)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Nenhum técnico encontrado.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($tecnicos as $tecnico): ?>
                            <tr>
                                <td><?= $tecnico['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div style="width: 15px; height: 15px; background-color: <?= $tecnico['cor'] ?>; margin-right: 10px; border-radius: 50%;"></div>
                                        <?= $tecnico['nome'] ?>
                                    </div>
                                </td>
                                <td><?= $tecnico['email'] ?></td>
                                <td><?= $tecnico['telefone'] ?></td>
                                <td><?= $tecnico['especialidade'] ?></td>
                                <td>
                                    <?php if ($tecnico['status'] == 'ativo'): ?>
                                    <span class="badge badge-success">Ativo</span>
                                    <?php else: ?>
                                    <span class="badge badge-danger">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/admin/tecnicos/agendamentos?id=<?= $tecnico['id'] ?>" class="btn btn-info btn-sm" title="Ver Agendamentos">
                                        <i class="fas fa-calendar"></i>
                                    </a>
                                    <a href="/admin/tecnicos/editar?id=<?= $tecnico['id'] ?>" class="btn btn-primary btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/admin/tecnicos/excluir?id=<?= $tecnico['id'] ?>" class="btn btn-danger btn-sm" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este técnico?')">
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
