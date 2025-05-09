<?php
// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/functions.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
    redirect('/admin-login.php');
}

// Conectar ao banco de dados
$db = db_connect();

// Obter todos os técnicos para o filtro
try {
    $stmt = $db->prepare("SELECT id, nome, cor FROM tecnicos WHERE status = 'ativo' ORDER BY nome");
    $stmt->execute();
    $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tecnicos = [];
}

// Obter todos os serviços para o filtro
try {
    $stmt = $db->prepare("SELECT id, titulo FROM servicos ORDER BY titulo");
    $stmt->execute();
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $servicos = [];
}

// Título da página
$page_title = "Calendário de Agendamentos";
$page_icon = "calendar-alt";

// Incluir o cabeçalho
include 'views/admin/includes/header.php';
?>

<!-- Sidebar -->
<?php include 'views/admin/includes/sidebar.php'; ?>

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">

        <!-- Begin Page Content -->
        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-<?= $page_icon ?> mr-2"></i> <?= $page_title ?>
                </h1>
                <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#novoAgendamentoModal">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Novo Agendamento
                </a>
            </div>

            <?php display_flash_message(); ?>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Calendário de Agendamentos</h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-filter"></i> Filtros
                        </button>
                        <div class="dropdown-menu dropdown-menu-right p-3" style="width: 300px;">
                            <form id="filterForm">
                                <div class="form-group">
                                    <label for="tecnico_id">Técnico</label>
                                    <select class="form-control form-control-sm" id="tecnico_id" name="tecnico_id">
                                        <option value="">Todos os técnicos</option>
                                        <?php foreach ($tecnicos as $tecnico): ?>
                                        <option value="<?= $tecnico['id'] ?>"><?= $tecnico['nome'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="servico_id">Serviço</label>
                                    <select class="form-control form-control-sm" id="servico_id" name="servico_id">
                                        <option value="">Todos os serviços</option>
                                        <?php foreach ($servicos as $servico): ?>
                                        <option value="<?= $servico['id'] ?>"><?= $servico['titulo'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control form-control-sm" id="status" name="status">
                                        <option value="">Todos os status</option>
                                        <option value="pendente">Pendente</option>
                                        <option value="confirmado">Confirmado</option>
                                        <option value="concluido">Concluído</option>
                                        <option value="cancelado">Cancelado</option>
                                    </select>
                                </div>
                                <div class="form-group mb-0">
                                    <button type="submit" class="btn btn-primary btn-sm btn-block">Aplicar Filtros</button>
                                    <button type="button" id="resetFilters" class="btn btn-secondary btn-sm btn-block mt-2">Limpar Filtros</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>

        </div>
        <!-- /.container-fluid -->

    </div>
    <!-- End of Main Content -->

    <!-- Footer -->
    <?php include 'views/admin/includes/footer.php'; ?>
    <!-- End of Footer -->

</div>
<!-- End of Content Wrapper -->

<!-- Modal Novo Agendamento -->
<div class="modal fade" id="novoAgendamentoModal" tabindex="-1" role="dialog" aria-labelledby="novoAgendamentoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="novoAgendamentoModalLabel">Novo Agendamento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="admin-save.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="form" value="agendamento">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label for="titulo">Título</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="pendente">Pendente</option>
                                <option value="confirmado">Confirmado</option>
                                <option value="concluido">Concluído</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="cliente_id">Cliente</label>
                            <select class="form-control" id="cliente_id" name="cliente_id" required>
                                <option value="">Selecione um cliente</option>
                                <?php
                                $stmt = $db->query("SELECT id, nome FROM clientes ORDER BY nome");
                                while ($cliente = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$cliente['id']}'>{$cliente['nome']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="servico_id">Serviço</label>
                            <select class="form-control" id="servico_id" name="servico_id" required>
                                <option value="">Selecione um serviço</option>
                                <?php
                                $stmt = $db->query("SELECT id, titulo FROM servicos ORDER BY titulo");
                                while ($servico = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$servico['id']}'>{$servico['titulo']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="tecnico_id">Técnico Principal</label>
                            <select class="form-control" id="tecnico_id" name="tecnico_id" required>
                                <option value="">Selecione um técnico</option>
                                <?php
                                foreach ($tecnicos as $tecnico) {
                                    echo "<option value='{$tecnico['id']}'>{$tecnico['nome']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="data_agendamento">Data</label>
                            <input type="date" class="form-control" id="data_agendamento" name="data_agendamento" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="hora_inicio">Hora Início</label>
                            <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="hora_fim">Hora Fim</label>
                            <input type="time" class="form-control" id="hora_fim" name="hora_fim">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="observacoes">Observações</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detalhes do Agendamento -->
<div class="modal fade" id="detalhesAgendamentoModal" tabindex="-1" role="dialog" aria-labelledby="detalhesAgendamentoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalhesAgendamentoModalLabel">Detalhes do Agendamento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Carregando...</span>
                    </div>
                </div>
                <div id="detalhesAgendamentoContent" style="display: none;">
                    <h4 id="agendamento_titulo" class="mb-3"></h4>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Cliente:</strong> <span id="agendamento_cliente"></span></p>
                            <p><strong>Serviço:</strong> <span id="agendamento_servico"></span></p>
                            <p><strong>Técnico:</strong> <span id="agendamento_tecnico"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Data:</strong> <span id="agendamento_data"></span></p>
                            <p><strong>Horário:</strong> <span id="agendamento_horario"></span></p>
                            <p><strong>Status:</strong> <span id="agendamento_status"></span></p>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Observações</h6>
                        </div>
                        <div class="card-body">
                            <p id="agendamento_observacoes">Nenhuma observação registrada.</p>
                        </div>
                    </div>
                    
                    <div id="agendamento_acoes" class="text-right">
                        <a href="#" id="btn_editar_agendamento" class="btn btn-info">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="#" id="btn_finalizar_agendamento" class="btn btn-success" data-toggle="modal" data-target="#finalizarAgendamentoModal">
                            <i class="fas fa-check"></i> Finalizar
                        </a>
                        <a href="#" id="btn_cancelar_agendamento" class="btn btn-danger">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Finalizar Agendamento -->
<div class="modal fade" id="finalizarAgendamentoModal" tabindex="-1" role="dialog" aria-labelledby="finalizarAgendamentoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="finalizarAgendamentoModalLabel">Finalizar Agendamento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="admin-save.php" method="post" id="formFinalizarAgendamento">
                <div class="modal-body">
                    <input type="hidden" name="form" value="finalizar_agendamento">
                    <input type="hidden" name="agendamento_id" id="finalizar_agendamento_id">
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="valor_cobrado">Valor Cobrado</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">R$</span>
                                </div>
                                <input type="text" class="form-control money" id="valor_cobrado" name="valor_cobrado" required>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="valor_pago">Valor Pago</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">R$</span>
                                </div>
                                <input type="text" class="form-control money" id="valor_pago" name="valor_pago" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="forma_pagamento">Forma de Pagamento</label>
                            <select class="form-control" id="forma_pagamento" name="forma_pagamento" required>
                                <option value="">Selecione</option>
                                <option value="Dinheiro">Dinheiro</option>
                                <option value="Cartão de Crédito">Cartão de Crédito</option>
                                <option value="Cartão de Débito">Cartão de Débito</option>
                                <option value="PIX">PIX</option>
                                <option value="Transferência Bancária">Transferência Bancária</option>
                                <option value="Boleto">Boleto</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="garantia_meses">Garantia (meses)</label>
                            <input type="number" class="form-control" id="garantia_meses" name="garantia_meses" min="0" value="3">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Técnicos que realizaram o serviço</label>
                        <div class="tecnicos-container">
                            <?php foreach ($tecnicos as $tecnico): ?>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="tecnico_<?= $tecnico['id'] ?>" name="tecnicos[]" value="<?= $tecnico['id'] ?>">
                                <label class="custom-control-label" for="tecnico_<?= $tecnico['id'] ?>"><?= $tecnico['nome'] ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="observacoes_finalizacao">Observações da Finalização</label>
                        <textarea class="form-control" id="observacoes_finalizacao" name="observacoes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Finalizar Agendamento</button>
                    <button type="button" class="btn btn-primary" id="btnGerarPDF">
                        <i class="fas fa-file-pdf"></i> Gerar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar máscara para valores monetários
        $('.money').mask('#.##0,00', {reverse: true});
        
        // Inicializar calendário
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            locale: 'pt-br',
            buttonText: {
                today: 'Hoje',
                month: 'Mês',
                week: 'Semana',
                day: 'Dia',
                list: 'Lista'
            },
            themeSystem: 'bootstrap',
            events: '/admin-calendario-json.php',
            eventClick: function(info) {
                exibirDetalhesAgendamento(info.event.id);
                return false;
            },
            dateClick: function(info) {
                // Preencher a data no modal de novo agendamento
                $('#data_agendamento').val(info.dateStr);
                $('#novoAgendamentoModal').modal('show');
            },
            eventContent: function(arg) {
                let timeText = arg.timeText;
                let title = arg.event.title;
                let status = arg.event.extendedProps.status || 'pendente';
                let cliente = arg.event.extendedProps.cliente || '';
                
                let statusClass = '';
                switch (status) {
                    case 'pendente': statusClass = 'warning'; break;
                    case 'confirmado': statusClass = 'primary'; break;
                    case 'concluido': statusClass = 'success'; break;
                    case 'cancelado': statusClass = 'danger'; break;
                }
                
                let content = document.createElement('div');
                content.classList.add('fc-event-content-wrapper');
                
                let timeEl = document.createElement('div');
                timeEl.classList.add('fc-event-time');
                timeEl.innerHTML = timeText;
                
                let titleEl = document.createElement('div');
                titleEl.classList.add('fc-event-title');
                titleEl.innerHTML = title;
                
                let clienteEl = document.createElement('div');
                clienteEl.classList.add('fc-event-cliente');
                clienteEl.innerHTML = cliente;
                
                let statusEl = document.createElement('div');
                statusEl.classList.add('fc-event-status', 'badge', 'badge-' + statusClass);
                statusEl.innerHTML = status.charAt(0).toUpperCase() + status.slice(1);
                
                content.appendChild(timeEl);
                content.appendChild(titleEl);
                content.appendChild(clienteEl);
                content.appendChild(statusEl);
                
                let arrayOfDomNodes = [content];
                return { domNodes: arrayOfDomNodes };
            },
            eventDidMount: function(info) {
                $(info.el).tooltip({
                    title: info.event.title + ' - ' + info.event.extendedProps.cliente,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            }
        });
        calendar.render();
        
        // Aplicar filtros
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            
            const tecnico_id = $('#tecnico_id').val();
            const servico_id = $('#servico_id').val();
            const status = $('#status').val();
            
            let url = '/admin-calendario-json.php?';
            if (tecnico_id) url += `tecnico_id=${tecnico_id}&`;
            if (servico_id) url += `servico_id=${servico_id}&`;
            if (status) url += `status=${status}&`;
            
            // Atualizar fonte de dados do calendário
            calendar.removeAllEventSources();
            calendar.addEventSource(url);
        });
        
        // Limpar filtros
        $('#resetFilters').on('click', function() {
            $('#tecnico_id').val('');
            $('#servico_id').val('');
            $('#status').val('');
            
            // Atualizar fonte de dados do calendário
            calendar.removeAllEventSources();
            calendar.addEventSource('/admin-calendario-json.php');
        });
        
        // Função para exibir detalhes do agendamento
        function exibirDetalhesAgendamento(id) {
            // Mostrar spinner e esconder conteúdo
            $('#detalhesAgendamentoContent').hide();
            $('.spinner-border').show();
            
            // Abrir modal
            $('#detalhesAgendamentoModal').modal('show');
            
            // Buscar dados do agendamento
            $.ajax({
                url: '/admin-calendario-json.php?action=get_details&id=' + id,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    // Preencher dados no modal
                    $('#agendamento_titulo').text(data.titulo);
                    $('#agendamento_cliente').text(data.cliente_nome);
                    $('#agendamento_servico').text(data.servico_titulo);
                    $('#agendamento_tecnico').text(data.tecnico_nome);
                    $('#agendamento_data').text(formatarData(data.data_agendamento));
                    $('#agendamento_horario').text(data.hora_inicio + (data.hora_fim ? ' - ' + data.hora_fim : ''));
                    
                    // Status com badge
                    let statusClass = '';
                    switch (data.status) {
                        case 'pendente': statusClass = 'warning'; break;
                        case 'confirmado': statusClass = 'primary'; break;
                        case 'concluido': statusClass = 'success'; break;
                        case 'cancelado': statusClass = 'danger'; break;
                    }
                    $('#agendamento_status').html('<span class="badge badge-' + statusClass + '">' + data.status.charAt(0).toUpperCase() + data.status.slice(1) + '</span>');
                    
                    // Observações
                    if (data.observacoes) {
                        $('#agendamento_observacoes').text(data.observacoes);
                    } else {
                        $('#agendamento_observacoes').text('Nenhuma observação registrada.');
                    }
                    
                    // Configurar botões de ação
                    $('#btn_editar_agendamento').attr('href', '/admin/agendamentos/editar?id=' + data.id);
                    
                    // Mostrar/esconder botão de finalizar conforme status
                    if (data.status === 'concluido' || data.status === 'cancelado') {
                        $('#btn_finalizar_agendamento').hide();
                    } else {
                        $('#btn_finalizar_agendamento').show();
                        $('#finalizar_agendamento_id').val(data.id);
                        
                        // Marcar o técnico principal
                        if (data.tecnico_id) {
                            $('#tecnico_' + data.tecnico_id).prop('checked', true);
                        }
                    }
                    
                    // Configurar botão de cancelar
                    $('#btn_cancelar_agendamento').attr('href', '/admin/agendamentos/cancelar?id=' + data.id);
                    if (data.status === 'cancelado' || data.status === 'concluido') {
                        $('#btn_cancelar_agendamento').hide();
                    } else {
                        $('#btn_cancelar_agendamento').show();
                    }
                    
                    // Esconder spinner e mostrar conteúdo
                    $('.spinner-border').hide();
                    $('#detalhesAgendamentoContent').show();
                },
                error: function() {
                    // Exibir mensagem de erro
                    $('#detalhesAgendamentoContent').html('<div class="alert alert-danger">Erro ao carregar detalhes do agendamento.</div>');
                    $('.spinner-border').hide();
                    $('#detalhesAgendamentoContent').show();
                }
            });
        }
        
        // Função para formatar data
        function formatarData(data) {
            const partes = data.split('-');
            return partes[2] + '/' + partes[1] + '/' + partes[0];
        }
        
        // Gerar PDF
        $('#btnGerarPDF').click(function(e) {
            e.preventDefault();
            
            // Validar formulário antes de gerar PDF
            if (!$('#formFinalizarAgendamento')[0].checkValidity()) {
                $('#formFinalizarAgendamento')[0].reportValidity();
                return;
            }
            
            // Obter ID do agendamento
            const agendamentoId = $('#finalizar_agendamento_id').val();
            
            // Redirecionar para a página de geração de PDF
            window.open('/gerar-pdf.php?tipo=agendamento&id=' + agendamentoId + '&' + $('#formFinalizarAgendamento').serialize(), '_blank');
        });
    });
</script>

<style>
    /* Estilos para os eventos no calendário */
    .fc-event {
        cursor: pointer;
        padding: 2px;
    }
    
    .fc-event-content-wrapper {
        padding: 2px;
        overflow: hidden;
    }
    
    .fc-event-time {
        font-weight: bold;
        font-size: 0.85em;
    }
    
    .fc-event-title {
        font-weight: bold;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .fc-event-cliente {
        font-size: 0.8em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .fc-event-status {
        font-size: 0.7em;
        margin-top: 2px;
        display: inline-block;
    }
    
    /* Estilos para o modal de finalização */
    .tecnicos-container {
        max-height: 150px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
    }
    
    /* Responsividade para dispositivos móveis */
    @media (max-width: 768px) {
        .fc-header-toolbar {
            flex-direction: column;
        }
        
        .fc-header-toolbar .fc-toolbar-chunk {
            margin-bottom: 10px;
        }
        
        .fc-event-content-wrapper {
            padding: 1px;
        }
        
        .fc-event-time, .fc-event-cliente, .fc-event-status {
            display: none;
        }
    }
</style>

<?php
// Incluir o footer
include 'views/admin/includes/footer.php';
?>
