<?php require 'views/admin/includes/header.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Novo Serviço</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/servicos">Serviços</a></li>
                    <li class="breadcrumb-item active">Novo</li>
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
                <h3 class="card-title">Adicionar Novo Serviço</h3>
            </div>
            <form method="POST" action="/admin/servicos/salvar">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="card-body">
                    <div class="form-group">
                        <label for="titulo">Título</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Título do serviço" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="icone">Ícone (Font Awesome)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-icons"></i></span>
                            </div>
                            <input type="text" class="form-control" id="icone" name="icone" placeholder="Ex: fan, snowflake, tools" value="fan">
                        </div>
                        <small class="form-text text-muted">Digite o nome do ícone do Font Awesome sem o prefixo "fa-".</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3" placeholder="Descrição do serviço" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="itens">Itens (um por linha)</label>
                        <textarea class="form-control" id="itens" name="itens" rows="5" placeholder="Digite um item por linha" required></textarea>
                        <small class="form-text text-muted">Cada linha será exibida como um item na lista de serviços.</small>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="/admin/servicos" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require 'views/admin/includes/footer.php'; ?>
