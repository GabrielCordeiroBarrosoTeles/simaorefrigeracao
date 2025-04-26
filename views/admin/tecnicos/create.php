<?php require 'views/admin/includes/header.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Adicionar Novo Técnico</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/tecnicos">Técnicos</a></li>
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
                <h3 class="card-title">Informações do Técnico</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/tecnicos/salvar">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nome">Nome *</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="telefone">Telefone *</label>
                                <input type="text" class="form-control" id="telefone" name="telefone" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="especialidade">Especialidade</label>
                                <input type="text" class="form-control" id="especialidade" name="especialidade">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cor">Cor</label>
                                <input type="color" class="form-control" id="cor" name="cor" value="#3b82f6">
                                <small class="form-text text-muted">Cor para identificação do técnico no calendário.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="ativo">Ativo</option>
                                    <option value="inativo">Inativo</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Associar a um Usuário</label>
                                <div class="custom-control custom-radio">
                                    <input class="custom-control-input" type="radio" id="usuario_existente" name="tipo_usuario" value="existente" checked>
                                    <label for="usuario_existente" class="custom-control-label">Usuário Existente</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input class="custom-control-input" type="radio" id="usuario_novo" name="tipo_usuario" value="novo">
                                    <label for="usuario_novo" class="custom-control-label">Criar Novo Usuário</label>
                                </div>
                            </div>
                            
                            <div id="usuario_existente_form">
                                <div class="form-group">
                                    <label for="usuario_id">Selecione o Usuário</label>
                                    <select class="form-control" id="usuario_id" name="usuario_id">
                                        <option value="">Selecione...</option>
                                        <?php foreach ($usuarios_disponiveis as $usuario): ?>
                                            <option value="<?= $usuario['id'] ?>"><?= $usuario['nome'] ?> (<?= $usuario['email'] ?>) - <?= $usuario['nivel'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div id="usuario_novo_form" style="display: none;">
                                <input type="hidden" name="criar_usuario" value="1">
                                
                                <div class="form-group">
                                    <label for="nivel_usuario">Nível de Acesso</label>
                                    <select class="form-control" id="nivel_usuario" name="nivel_usuario">
                                        <option value="tecnico">Técnico</option>
                                        <option value="tecnico_adm">Técnico Administrativo</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="senha">Senha</label>
                                    <input type="password" class="form-control" id="senha" name="senha">
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirmar_senha">Confirmar Senha</label>
                                    <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar
                            </button>
                            <a href="/admin/tecnicos" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Alternar entre usuário existente e novo usuário
    const radioExistente = document.getElementById('usuario_existente');
    const radioNovo = document.getElementById('usuario_novo');
    const formExistente = document.getElementById('usuario_existente_form');
    const formNovo = document.getElementById('usuario_novo_form');
    
    radioExistente.addEventListener('change', function() {
        if (this.checked) {
            formExistente.style.display = 'block';
            formNovo.style.display = 'none';
        }
    });
    
    radioNovo.addEventListener('change', function() {
        if (this.checked) {
            formExistente.style.display = 'none';
            formNovo.style.display = 'block';
        }
    });
    
    // Validar formulário antes de enviar
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        if (radioNovo.checked) {
            const senha = document.getElementById('senha').value;
            const confirmarSenha = document.getElementById('confirmar_senha').value;
            
            if (senha !== confirmarSenha) {
                e.preventDefault();
                alert('As senhas não coincidem. Por favor, verifique.');
            }
        }
    });
});
</script>

<?php require 'views/admin/includes/footer.php'; ?>
