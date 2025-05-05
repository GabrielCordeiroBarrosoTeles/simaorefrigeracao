<?php
require_once 'bootstrap.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
    redirect('/admin/login');
}

// Verificar se foi fornecido um formulário válido
$form = isset($_GET['form']) ? $_GET['form'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Conexão com o banco de dados
$db = db_connect();

// Definir formulários disponíveis
$forms = [
    'cliente' => [
        'title' => 'Cliente',
        'table' => 'clientes',
        'fields' => [
            'nome' => ['label' => 'Nome', 'type' => 'text', 'required' => true],
            'email' => ['label' => 'Email', 'type' => 'email', 'required' => true],
            'telefone' => ['label' => 'Telefone', 'type' => 'text', 'required' => true],
            'endereco' => ['label' => 'Endereço', 'type' => 'text'],
            'cidade' => ['label' => 'Cidade', 'type' => 'text'],
            'estado' => ['label' => 'Estado', 'type' => 'text'],
            'cep' => ['label' => 'CEP', 'type' => 'text'],
            'tipo' => [
                'label' => 'Tipo', 
                'type' => 'select', 
                'options' => [
                    'residencial' => 'Residencial',
                    'comercial' => 'Comercial',
                    'industrial' => 'Industrial'
                ]
            ],
            'observacoes' => ['label' => 'Observações', 'type' => 'textarea']
        ]
    ],
    'tecnico' => [
        'title' => 'Técnico',
        'table' => 'tecnicos',
        'fields' => [
            'nome' => ['label' => 'Nome', 'type' => 'text', 'required' => true],
            'email' => ['label' => 'Email', 'type' => 'email', 'required' => true],
            'telefone' => ['label' => 'Telefone', 'type' => 'text', 'required' => true],
            'especialidade' => ['label' => 'Especialidade', 'type' => 'text'],
            'cor' => ['label' => 'Cor', 'type' => 'color', 'default' => '#3b82f6'],
            'status' => [
                'label' => 'Status', 
                'type' => 'select', 
                'options' => [
                    'ativo' => 'Ativo',
                    'inativo' => 'Inativo'
                ]
            ]
        ]
    ],
    'servico' => [
        'title' => 'Serviço',
        'table' => 'servicos',
        'fields' => [
            'nome' => ['label' => 'Nome', 'type' => 'text', 'required' => true],
            'descricao' => ['label' => 'Descrição', 'type' => 'textarea', 'required' => true],
            'preco' => ['label' => 'Preço', 'type' => 'number', 'step' => '0.01', 'required' => true],
            'duracao' => ['label' => 'Duração (minutos)', 'type' => 'number', 'required' => true],
            'garantia_meses' => ['label' => 'Garantia (meses)', 'type' => 'number', 'default' => 3],
            'destaque' => ['label' => 'Destaque', 'type' => 'checkbox'],
            'ativo' => ['label' => 'Ativo', 'type' => 'checkbox', 'default' => true]
        ]
    ],
    'agendamento' => [
        'title' => 'Agendamento',
        'table' => 'agendamentos',
        'fields' => [
            'cliente_id' => [
                'label' => 'Cliente', 
                'type' => 'select', 
                'options_query' => "SELECT id, nome FROM clientes ORDER BY nome ASC",
                'required' => true
            ],
            'servico_id' => [
                'label' => 'Serviço', 
                'type' => 'select', 
                'options_query' => "SELECT id, nome FROM servicos WHERE ativo = 1 ORDER BY nome ASC",
                'required' => true
            ],
            'tecnico_id' => [
                'label' => 'Técnico', 
                'type' => 'select', 
                'options_query' => "SELECT id, nome FROM tecnicos WHERE status = 'ativo' ORDER BY nome ASC",
                'required' => true
            ],
            'data_agendamento' => ['label' => 'Data', 'type' => 'date', 'required' => true],
            'hora_inicio' => ['label' => 'Hora Início', 'type' => 'time', 'required' => true],
            'hora_fim' => ['label' => 'Hora Fim', 'type' => 'time'],
            'local_servico' => ['label' => 'Local do Serviço', 'type' => 'text'],
            'valor' => ['label' => 'Valor (R$)', 'type  => 'Local do Serviço', 'type' => 'text'],
            'valor' => ['label' => 'Valor (R$)', 'type' => 'number', 'step' => '0.01', 'default' => '0.00'],
            'valor_pendente' => ['label' => 'Valor Pendente (R$)', 'type' => 'number', 'step' => '0.01', 'default' => '0.00'],
            'status' => [
                'label' => 'Status', 
                'type' => 'select', 
                'options' => [
                    'pendente' => 'Pendente',
                    'confirmado' => 'Confirmado',
                    'em_andamento' => 'Em Andamento',
                    'concluido' => 'Concluído',
                    'cancelado' => 'Cancelado'
                ],
                'required' => true
            ],
            'observacoes' => ['label' => 'Observações', 'type' => 'textarea'],
            'observacoes_tecnicas' => ['label' => 'Observações Técnicas', 'type' => 'textarea'],
            'data_garantia' => ['label' => 'Data de Término da Garantia', 'type' => 'date']
        ]
    ]
];

// Verificar se o formulário existe
if (!isset($forms[$form])) {
    set_flash_message('danger', 'Formulário inválido.');
    redirect('/admin-dashboard.php');
}

$form_config = $forms[$form];
$data = [];

// Buscar dados existentes se for edição
if ($id > 0) {
    try {
        $query = "SELECT * FROM {$form_config['table']} WHERE id = :id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            set_flash_message('danger', 'Registro não encontrado.');
            redirect('/admin-table.php?table=' . $form_config['table']);
        }
        
        // Se for um agendamento, calcular a data de garantia se não estiver definida
        if ($form === 'agendamento' && empty($data['data_garantia']) && $data['status'] === 'concluido') {
            // Buscar a garantia do serviço
            $query = "SELECT garantia_meses FROM servicos WHERE id = :id LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $data['servico_id']);
            $stmt->execute();
            $servico = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($servico) {
                $garantia_meses = $servico['garantia_meses'] ?? 3; // Padrão de 3 meses
                $data_conclusao = new DateTime($data['data_agendamento']);
                $data_garantia = clone $data_conclusao;
                $data_garantia->modify("+{$garantia_meses} months");
                $data['data_garantia'] = $data_garantia->format('Y-m-d');
            }
        }
    } catch (PDOException $e) {
        set_flash_message('danger', 'Erro ao buscar dados: ' . $e->getMessage());
        redirect('/admin-table.php?table=' . $form_config['table']);
    }
}

// Incluir cabeçalho
include 'views/admin/includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $id > 0 ? 'Editar' : 'Adicionar' ?> <?= $form_config['title'] ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="admin-dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="admin-table.php?table=<?= $form_config['table'] ?>"><?= $form_config['title'] ?>s</a></li>
        <li class="breadcrumb-item active"><?= $id > 0 ? 'Editar' : 'Adicionar' ?></li>
    </ol>
    
    <?php display_flash_message(); ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            <?= $id > 0 ? 'Editar' : 'Adicionar' ?> <?= $form_config['title'] ?>
        </div>
        <div class="card-body">
            <form method="post" action="admin-save.php">
                <input type="hidden" name="form" value="<?= $form ?>">
                <input type="hidden" name="id" value="<?= $id ?>">
                
                <div class="row">
                    <?php foreach ($form_config['fields'] as $field_name => $field): ?>
                        <div class="col-md-6 mb-3">
                            <label for="<?= $field_name ?>" class="form-label">
                                <?= $field['label'] ?>
                                <?= isset($field['required']) && $field['required'] ? '<span class="text-danger">*</span>' : '' ?>
                            </label>
                            
                            <?php if ($field['type'] === 'text' || $field['type'] === 'email' || $field['type'] === 'number' || $field['type'] === 'date' || $field['type'] === 'time' || $field['type'] === 'color'): ?>
                                <input 
                                    type="<?= $field['type'] ?>" 
                                    class="form-control" 
                                    id="<?= $field_name ?>" 
                                    name="<?= $field_name ?>" 
                                    value="<?= isset($data[$field_name]) ? htmlspecialchars($data[$field_name]) : (isset($field['default']) ? $field['default'] : '') ?>"
                                    <?= isset($field['required']) && $field['required'] ? 'required' : '' ?>
                                    <?= isset($field['step']) ? 'step="' . $field['step'] . '"' : '' ?>
                                >
                            <?php elseif ($field['type'] === 'textarea'): ?>
                                <textarea 
                                    class="form-control" 
                                    id="<?= $field_name ?>" 
                                    name="<?= $field_name ?>" 
                                    rows="3"
                                    <?= isset($field['required']) && $field['required'] ? 'required' : '' ?>
                                ><?= isset($data[$field_name]) ? htmlspecialchars($data[$field_name]) : (isset($field['default']) ? $field['default'] : '') ?></textarea>
                            <?php elseif ($field['type'] === 'select'): ?>
                                <select 
                                    class="form-select" 
                                    id="<?= $field_name ?>" 
                                    name="<?= $field_name ?>"
                                    <?= isset($field['required']) && $field['required'] ? 'required' : '' ?>
                                >
                                    <option value="">Selecione...</option>
                                    <?php 
                                    if (isset($field['options_query'])) {
                                        // Opções dinâmicas do banco de dados
                                        try {
                                            $stmt = $db->prepare($field['options_query']);
                                            $stmt->execute();
                                            $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            
                                            foreach ($options as $option) {
                                                $selected = isset($data[$field_name]) && $data[$field_name] == $option['id'] ? 'selected' : '';
                                                echo "<option value=\"{$option['id']}\" {$selected}>{$option['nome']}</option>";
                                            }
                                        } catch (PDOException $e) {
                                            echo "<option value=\"\">Erro ao carregar opções</option>";
                                        }
                                    } else {
                                        // Opções estáticas
                                        foreach ($field['options'] as $value => $label) {
                                            $selected = isset($data[$field_name]) && $data[$field_name] == $value ? 'selected' : '';
                                            echo "<option value=\"{$value}\" {$selected}>{$label}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            <?php elseif ($field['type'] === 'checkbox'): ?>
                                <div class="form-check">
                                    <input 
                                        type="checkbox" 
                                        class="form-check-input" 
                                        id="<?= $field_name ?>" 
                                        name="<?= $field_name ?>" 
                                        value="1"
                                        <?= isset($data[$field_name]) && $data[$field_name] ? 'checked' : (isset($field['default']) && $field['default'] ? 'checked' : '') ?>
                                    >
                                    <label class="form-check-label" for="<?= $field_name ?>"><?= $field['label'] ?></label>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="admin-table.php?table=<?= $form_config['table'] ?>" class="btn btn-secondary">Cancelar</a>
                    
                    <?php if ($form === 'agendamento' && $id > 0): ?>
                        <a href="exportar-xml.php?id=<?= $id ?>" class="btn btn-success">
                            <i class="fas fa-file-export"></i> Exportar XML
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <?php if ($form === 'agendamento' && $id > 0): ?>
        <!-- Seção de Garantia -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-shield-alt me-1"></i>
                Informações de Garantia
            </div>
            <div class="card-body">
                <?php
                $tem_garantia = !empty($data['data_garantia']);
                $garantia_expirada = false;
                
                if ($tem_garantia) {
                    $data_atual = new DateTime();
                    $data_garantia = new DateTime($data['data_garantia']);
                    $garantia_expirada = $data_atual > $data_garantia;
                }
                
                if ($data['status'] === 'concluido'):
                    if ($tem_garantia):
                ?>
                    <div class="alert <?= $garantia_expirada ? 'alert-danger' : 'alert-success' ?>">
                        <h5><i class="fas fa-<?= $garantia_expirada ? 'times-circle' : 'check-circle' ?>"></i> 
                            <?= $garantia_expirada ? 'Garantia Expirada' : 'Garantia Válida' ?>
                        </h5>
                        <p>
                            Este serviço possui garantia até <strong><?= date('d/m/Y', strtotime($data['data_garantia'])) ?></strong>.
                            <?php if ($garantia_expirada): ?>
                                <br>A garantia deste serviço já expirou.
                            <?php else: ?>
                                <?php
                                $data_atual = new DateTime();
                                $data_garantia = new DateTime($data['data_garantia']);
                                $diff = $data_atual->diff($data_garantia);
                                $dias_restantes = $diff->days;
                                ?>
                                <br>Restam <strong><?= $dias_restantes ?> dias</strong> de garantia.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Garantia Não Definida</h5>
                        <p>
                            Este serviço não possui uma data de garantia definida. 
                            Edite o agendamento para definir a data de término da garantia.
                        </p>
                    </div>
                <?php 
                    endif;
                else:
                ?>
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Serviço Não Concluído</h5>
                        <p>
                            A garantia será aplicada automaticamente quando o serviço for marcado como concluído.
                            Por padrão, a garantia é de 3 meses a partir da data de conclusão do serviço.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Atualizar valor pendente quando o valor total for alterado
    const valorInput = document.getElementById('valor');
    const valorPendenteInput = document.getElementById('valor_pendente');
    
    if (valorInput && valorPendenteInput) {
        valorInput.addEventListener('change', function() {
            if (valorPendenteInput.value === '' || parseFloat(valorPendenteInput.value) > parseFloat(valorInput.value)) {
                valorPendenteInput.value = valorInput.value;
            }
        });
    }
    
    // Calcular data de garantia quando o status mudar para concluído
    const statusSelect = document.getElementById('status');
    const dataGarantiaInput = document.getElementById('data_garantia');
    const servicoSelect = document.getElementById('servico_id');
    
    if (statusSelect && dataGarantiaInput && servicoSelect) {
        statusSelect.addEventListener('change', function() {
            if (this.value === 'concluido' && dataGarantiaInput.value === '') {
                // Buscar garantia do serviço via AJAX
                const servicoId = servicoSelect.value;
                if (servicoId) {
                    fetch(`get-garantia.php?servico_id=${servicoId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                dataGarantiaInput.value = data.data_garantia;
                            }
                        })
                        .catch(error => console.error('Erro:', error));
                }
            }
        });
    }
});
</script>

<?php include 'views/admin/includes/footer.php'; ?>
