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
        
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Filtros e Ações</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="text" id="pesquisar-tecnico" class="form-control" placeholder="Pesquisar técnico por nome, email ou especialidade...">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button" id="btn-pesquisar">
                                            <i class="fas fa-search"></i> Pesquisar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="btn-group float-right">
                                    <a href="/admin/tecnicos/novo" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Novo Técnico
                                    </a>
                                    <a href="/" target="_blank" class="btn btn-secondary">
                                        <i class="fas fa-external-link-alt"></i> Ver Site
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <?php if (empty($tecnicos)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i> Nenhum técnico encontrado. Clique em "Novo Técnico" para adicionar.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($tecnicos as $tecnico): ?>
                    <div class="col-md-4 col-sm-6 tecnico-card" 
                         data-nome="<?= strtolower($tecnico['nome']) ?>" 
                         data-email="<?= strtolower($tecnico['email']) ?>" 
                         data-especialidade="<?= strtolower($tecnico['especialidade']) ?>">
                        <div class="card card-widget widget-user">
                            <!-- Add the bg color to the header using any of the bg-* classes -->
                            <div class="widget-user-header text-white" style="background-color: <?= $tecnico['cor'] ?>;">
                                <h3 class="widget-user-username"><?= $tecnico['nome'] ?></h3>
                                <h5 class="widget-user-desc"><?= $tecnico['especialidade'] ?></h5>
                            </div>
                            <div class="widget-user-image">
                                <img class="img-circle elevation-2" src="https://ui-avatars.com/api/?name=<?= urlencode($tecnico['nome']) ?>&background=<?= urlencode(str_replace('#', '', $tecnico['cor'])) ?>&color=fff&size=128" alt="Foto do Técnico">
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header" id="total-agendamentos-<?= $tecnico['id'] ?>">...</h5>
                                            <span class="description-text">AGENDAMENTOS</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header" id="total-concluidos-<?= $tecnico['id'] ?>">...</h5>
                                            <span class="description-text">CONCLUÍDOS</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="description-block">
                                            <h5 class="description-header">
                                                <?php if ($tecnico['status'] == 'ativo'): ?>
                                                    <span class="badge badge-success">Ativo</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inativo</span>
                                                <?php endif; ?>
                                            </h5>
                                            <span class="description-text">STATUS</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="tecnico-contato mb-2">
                                            <i class="fas fa-envelope text-muted mr-1"></i> <?= $tecnico['email'] ?>
                                        </div>
                                        <div class="tecnico-contato mb-3">
                                            <i class="fas fa-phone text-muted mr-1"></i> <?= $tecnico['telefone'] ?>
                                        </div>
                                        <div class="btn-group w-100">
                                            <a href="/admin/tecnicos/agendamentos?id=<?= $tecnico['id'] ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-calendar"></i> Agendamentos
                                            </a>
                                            <a href="/admin/tecnicos/editar?id=<?= $tecnico['id'] ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <a href="/admin/tecnicos/excluir?id=<?= $tecnico['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este técnico?')">
                                                <i class="fas fa-trash"></i> Excluir
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
    .tecnico-card {
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }
    .tecnico-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .widget-user .widget-user-header {
        height: 120px;
        border-top-left-radius: 0.25rem;
        border-top-right-radius: 0.25rem;
    }
    .widget-user .widget-user-username {
        margin-top: 0;
        margin-bottom: 5px;
        font-size: 20px;
        font-weight: 600;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
    }
    .widget-user .widget-user-desc {
        margin-top: 0;
        font-size: 14px;
    }
    .widget-user .widget-user-image {
        position: absolute;
        top: 85px;
        left: 50%;
        margin-left: -45px;
    }
    .widget-user .widget-user-image img {
        width: 90px;
        height: 90px;
        border: 3px solid #fff;
    }
    .widget-user .card-footer {
        padding-top: 50px;
    }
    .description-block {
        margin: 10px 0;
        text-align: center;
    }
    .description-block .description-header {
        margin: 0;
        padding: 0;
        font-weight: 600;
        font-size: 16px;
    }
    .description-block .description-text {
        font-size: 12px;
        color: #6c757d;
    }
    .tecnico-contato {
        font-size: 14px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Responsividade */
    @media (max-width: 768px) {
        .btn-group {
            display: flex;
            flex-direction: column;
        }
        .btn-group .btn {
            border-radius: 0.25rem !important;
            margin-bottom: 5px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Carregar estatísticas para cada técnico
    <?php foreach ($tecnicos as $tecnico): ?>
        carregarEstatisticas(<?= $tecnico['id'] ?>);
    <?php endforeach; ?>
    
    // Função para carregar estatísticas
    function carregarEstatisticas(tecnicoId) {
        fetch('/admin/tecnicos/api?action=stats&id=' + tecnicoId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('total-agendamentos-' + tecnicoId).textContent = data.total || 0;
                    document.getElementById('total-concluidos-' + tecnicoId).textContent = data.concluidos || 0;
                } else {
                    document.getElementById('total-agendamentos-' + tecnicoId).textContent = '0';
                    document.getElementById('total-concluidos-' + tecnicoId).textContent = '0';
                }
            })
            .catch(error => {
                console.error('Erro ao carregar estatísticas:', error);
                document.getElementById('total-agendamentos-' + tecnicoId).textContent = 'Erro';
                document.getElementById('total-concluidos-' + tecnicoId).textContent = 'Erro';
            });
    }
    
    // Pesquisar técnicos
    document.getElementById('btn-pesquisar').addEventListener('click', pesquisarTecnicos);
    document.getElementById('pesquisar-tecnico').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            pesquisarTecnicos();
        }
    });
    
    function pesquisarTecnicos() {
        const termo = document.getElementById('pesquisar-tecnico').value.toLowerCase();
        const cards = document.querySelectorAll('.tecnico-card');
        
        cards.forEach(card => {
            const nome = card.getAttribute('data-nome');
            const email = card.getAttribute('data-email');
            const especialidade = card.getAttribute('data-especialidade');
            
            if (nome.includes(termo) || email.includes(termo) || especialidade.includes(termo) || termo === '') {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
});
</script>

<?php require 'views/admin/includes/footer.php'; ?>
