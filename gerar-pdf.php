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

// Verificar tipo de documento e ID
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (empty($tipo) || $id <= 0) {
    die('Parâmetros inválidos');
}

// Conectar ao banco de dados
$db = db_connect();

// Gerar PDF conforme o tipo
switch ($tipo) {
    case 'agendamento':
        gerarPDFAgendamento($id);
        break;
    
    case 'relatorio':
        $periodo = isset($_GET['periodo']) ? $_GET['periodo'] : '';
        if (empty($periodo)) {
            die('Parâmetros inválidos');
        }
        gerarPDFRelatorio($periodo);
        break;
    
    default:
        die('Tipo de documento não suportado');
}

// Função para gerar PDF de agendamento
function gerarPDFAgendamento($id) {
    global $db;
    
    try {
        // Buscar dados do agendamento
        $sql = "SELECT a.*, 
                c.nome as cliente_nome, c.telefone as cliente_telefone, c.email as cliente_email, c.endereco as cliente_endereco,
                s.titulo as servico_titulo, s.descricao as servico_descricao,
                t.nome as tecnico_nome, t.telefone as tecnico_telefone
                FROM agendamentos a
                LEFT JOIN clientes c ON a.cliente_id = c.id
                LEFT JOIN servicos s ON a.servico_id = s.id
                LEFT JOIN tecnicos t ON a.tecnico_id = t.id
                WHERE a.id = :id";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$agendamento) {
            die('Agendamento não encontrado');
        }
        
        // Buscar técnicos adicionais
        $sql = "SELECT t.nome 
                FROM agendamento_tecnicos at
                JOIN tecnicos t ON at.tecnico_id = t.id
                WHERE at.agendamento_id = :agendamento_id";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':agendamento_id', $id);
        $stmt->execute();
        
        $tecnicos_adicionais = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Obter valores do formulário de finalização
        $valor_cobrado = isset($_GET['valor_cobrado']) ? str_replace(['R$', '.', ','], ['', '', '.'], $_GET['valor_cobrado']) : $agendamento['valor_cobrado'];
        $valor_pago = isset($_GET['valor_pago']) ? str_replace(['R$', '.', ','], ['', '', '.'], $_GET['valor_pago']) : $agendamento['valor_pago'];
        $forma_pagamento = isset($_GET['forma_pagamento']) ? $_GET['forma_pagamento'] : $agendamento['forma_pagamento'];
        $garantia_meses = isset($_GET['garantia_meses']) ? (int)$_GET['garantia_meses'] : $agendamento['garantia_meses'];
        $observacoes = isset($_GET['observacoes']) ? $_GET['observacoes'] : '';
        
        // Calcular data de garantia
        $data_garantia = date('Y-m-d', strtotime("+{$garantia_meses} months"));
        
        // Iniciar buffer de saída
        ob_start();
        
        // HTML do PDF
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <title>Comprovante de Serviço - <?= htmlspecialchars($agendamento['titulo']) ?></title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 20px;
                }
                .container {
                    max-width: 800px;
                    margin: 0 auto;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #0056b3;
                    padding-bottom: 10px;
                }
                .logo {
                    max-width: 200px;
                    margin-bottom: 10px;
                }
                h1 {
                    color: #0056b3;
                    margin: 0;
                    font-size: 24px;
                }
                h2 {
                    color: #0056b3;
                    font-size: 18px;
                    margin-top: 20px;
                    border-bottom: 1px solid #ddd;
                    padding-bottom: 5px;
                }
                .info-block {
                    margin-bottom: 20px;
                }
                .info-row {
                    display: flex;
                    margin-bottom: 5px;
                }
                .info-label {
                    font-weight: bold;
                    width: 200px;
                }
                .info-value {
                    flex: 1;
                }
                .footer {
                    margin-top: 50px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                    border-top: 1px solid #ddd;
                    padding-top: 20px;
                }
                .signature {
                    margin-top: 50px;
                    display: flex;
                    justify-content: space-between;
                }
                .signature-line {
                    width: 45%;
                    border-top: 1px solid #333;
                    padding-top: 5px;
                    text-align: center;
                }
                .garantia {
                    margin-top: 30px;
                    border: 1px solid #0056b3;
                    padding: 10px;
                    background-color: #f0f7ff;
                }
                .garantia h3 {
                    margin-top: 0;
                    color: #0056b3;
                }
                @media print {
                    body {
                        padding: 0;
                    }
                    .no-print {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="/assets/img/logo.png" alt="Simão Refrigeração" class="logo">
                    <h1>Simão Refrigeração</h1>
                    <p>Serviços de instalação, manutenção e projetos de ar condicionado</p>
                </div>
                
                <h1>Comprovante de Serviço</h1>
                <p>Nº <?= str_pad($agendamento['id'], 6, '0', STR_PAD_LEFT) ?> - Data: <?= date('d/m/Y') ?></p>
                
                <h2>Dados do Cliente</h2>
                <div class="info-block">
                    <div class="info-row">
                        <div class="info-label">Nome:</div>
                        <div class="info-value"><?= htmlspecialchars($agendamento['cliente_nome']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Telefone:</div>
                        <div class="info-value"><?= htmlspecialchars($agendamento['cliente_telefone']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email:</div>
                        <div class="info-value"><?= htmlspecialchars($agendamento['cliente_email']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Endereço:</div>
                        <div class="info-value"><?= htmlspecialchars($agendamento['cliente_endereco']) ?></div>
                    </div>
                </div>
                
                <h2>Dados do Serviço</h2>
                <div class="info-block">
                    <div class="info-row">
                        <div class="info-label">Serviço:</div>
                        <div class="info-value"><?= htmlspecialchars($agendamento['servico_titulo']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Descrição:</div>
                        <div class="info-value"><?= htmlspecialchars($agendamento['servico_descricao']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Data de Execução:</div>
                        <div class="info-value"><?= date('d/m/Y', strtotime($agendamento['data_agendamento'])) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Horário:</div>
                        <div class="info-value">
                            <?= date('H:i', strtotime($agendamento['hora_inicio'])) ?>
                            <?= $agendamento['hora_fim'] ? ' - ' . date('H:i', strtotime($agendamento['hora_fim'])) : '' ?>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Técnico Principal:</div>
                        <div class="info-value"><?= htmlspecialchars($agendamento['tecnico_nome']) ?></div>
                    </div>
                    <?php if (!empty($tecnicos_adicionais)): ?>
                    <div class="info-row">
                        <div class="info-label">Técnicos Adicionais:</div>
                        <div class="info-value"><?= htmlspecialchars(implode(', ', $tecnicos_adicionais)) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <h2>Valores</h2>
                <div class="info-block">
                    <div class="info-row">
                        <div class="info-label">Valor Cobrado:</div>
                        <div class="info-value">R$ <?= number_format($valor_cobrado, 2, ',', '.') ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Valor Pago:</div>
                        <div class="info-value">R$ <?= number_format($valor_pago, 2, ',', '.') ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Forma de Pagamento:</div>
                        <div class="info-value"><?= htmlspecialchars($forma_pagamento) ?></div>
                    </div>
                </div>
                
                <?php if (!empty($observacoes) || !empty($agendamento['observacoes'])): ?>
                <h2>Observações</h2>
                <div class="info-block">
                    <?php if (!empty($agendamento['observacoes'])): ?>
                    <p><?= nl2br(htmlspecialchars($agendamento['observacoes'])) ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($observacoes)): ?>
                    <p><?= nl2br(htmlspecialchars($observacoes)) ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="garantia">
                    <h3>Garantia</h3>
                    <p>Este serviço possui garantia de <strong><?= $garantia_meses ?> meses</strong>, válida até <strong><?= date('d/m/Y', strtotime($data_garantia)) ?></strong>.</p>
                    <p>A garantia cobre defeitos relacionados ao serviço prestado, não incluindo danos causados por mau uso, acidentes ou desgaste natural.</p>
                </div>
                
                <div class="signature">
                    <div class="signature-line">
                        Técnico Responsável
                    </div>
                    <div class="signature-line">
                        Cliente
                    </div>
                </div>
                
                <div class="footer">
                    <p>Simão Refrigeração - CNPJ: XX.XXX.XXX/0001-XX</p>
                    <p>Endereço: Rua Exemplo, 123 - Bairro - Cidade/UF - CEP: 00000-000</p>
                    <p>Telefone: (XX) XXXX-XXXX - Email: contato@simaorefrigeracao.com.br</p>
                    <p>www.simaorefrigeracao.com.br</p>
                </div>
                
                <div class="no-print" style="margin-top: 20px; text-align: center;">
                    <button onclick="window.print();" style="padding: 10px 20px; background-color: #0056b3; color: white; border: none; border-radius: 4px; cursor: pointer;">Imprimir</button>
                </div>
            </div>
            
            <script>
                // Imprimir automaticamente
                window.onload = function() {
                    setTimeout(function() {
                        window.print();
                    }, 1000);
                };
            </script>
        </body>
        </html>
        <?php
        
        // Obter conteúdo do buffer
        $html = ob_get_clean();
        
        // Exibir HTML
        echo $html;
    } catch (PDOException $e) {
        die('Erro ao gerar PDF: ' . ($DEBUG_MODE ? $e->getMessage() : 'Erro interno'));
    }
}

// Função para gerar PDF de relatório
function gerarPDFRelatorio($periodo) {
    global $db;
    
    try {
        // Definir período
        $data_inicio = '';
        $data_fim = date('Y-m-d');
        $titulo_periodo = '';
        
        switch ($periodo) {
            case 'diario':
                $data_inicio = date('Y-m-d');
                $titulo_periodo = 'Diário - ' . date('d/m/Y');
                break;
            
            case 'semanal':
                $data_inicio = date('Y-m-d', strtotime('-7 days'));
                $titulo_periodo = 'Semanal - ' . date('d/m/Y', strtotime('-7 days')) . ' até ' . date('d/m/Y');
                break;
            
            case 'mensal':
                $data_inicio = date('Y-m-01');
                $titulo_periodo = 'Mensal - ' . date('m/Y');
                break;
            
            case 'trimestral':
                $data_inicio = date('Y-m-d', strtotime('-3 months'));
                $titulo_periodo = 'Trimestral - ' . date('d/m/Y', strtotime('-3 months')) . ' até ' . date('d/m/Y');
                break;
            
            case 'anual':
                $data_inicio = date('Y-01-01');
                $titulo_periodo = 'Anual - ' . date('Y');
                break;
            
            default:
                die('Período inválido');
        }
        
        // Buscar dados de agendamentos concluídos
        $sql = "SELECT 
                COUNT(*) as total_agendamentos,
                SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) as total_concluidos,
                SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as total_cancelados,
                SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as total_pendentes,
                SUM(CASE WHEN status = 'confirmado' THEN 1 ELSE 0 END) as total_confirmados,
                SUM(CASE WHEN status = 'concluido' THEN valor_cobrado ELSE 0 END) as valor_total,
                SUM(CASE WHEN status = 'concluido' THEN valor_pago ELSE 0 END) as valor_pago_total
                FROM agendamentos
                WHERE data_agendamento BETWEEN :data_inicio AND :data_fim";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);
        $stmt->execute();
        
        $resumo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Buscar serviços mais realizados
        $sql = "SELECT s.titulo, COUNT(*) as total
                FROM agendamentos a
                JOIN servicos s ON a.servico_id = s.id
                WHERE a.status = 'concluido' AND a.data_agendamento BETWEEN :data_inicio AND :data_fim
                GROUP BY a.servico_id
                ORDER BY total DESC
                LIMIT 5";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);
        $stmt->execute();
        
        $servicos_populares = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar técnicos mais produtivos
        $sql = "SELECT t.nome, COUNT(*) as total
                FROM agendamentos a
                JOIN tecnicos t ON a.tecnico_id = t.id
                WHERE a.status = 'concluido' AND a.data_agendamento BETWEEN :data_inicio AND :data_fim
                GROUP BY a.tecnico_id
                ORDER BY total DESC
                LIMIT 5";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);
        $stmt->execute();
        
        $tecnicos_produtivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar clientes mais frequentes
        $sql = "SELECT c.nome, COUNT(*) as total
                FROM agendamentos a
                JOIN clientes c ON a.cliente_id = c.id
                WHERE a.status = 'concluido' AND a.data_agendamento BETWEEN :data_inicio AND :data_fim
                GROUP BY a.cliente_id
                ORDER BY total DESC
                LIMIT 5";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);
        $stmt->execute();
        
        $clientes_frequentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar últimos agendamentos concluídos
        $sql = "SELECT a.id, a.titulo, a.data_agendamento, a.valor_cobrado, c.nome as cliente_nome, s.titulo as servico_titulo
                FROM agendamentos a
                JOIN clientes c ON a.cliente_id = c.id
                JOIN servicos s ON a.servico_id = s.id
                WHERE a.status = 'concluido' AND a.data_agendamento BETWEEN :data_inicio AND :data_fim
                ORDER BY a.data_agendamento DESC
                LIMIT 10";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);
        $stmt->execute();
        
        $ultimos_agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Iniciar buffer de saída
        ob_start();
        
        // HTML do PDF
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <title>Relatório <?= $titulo_periodo ?> - Simão Refrigeração</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 20px;
                }
                .container {
                    max-width: 800px;
                    margin: 0 auto;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #0056b3;
                    padding-bottom: 10px;
                }
                .logo {
                    max-width: 200px;
                    margin-bottom: 10px;
                }
                h1 {
                    color: #0056b3;
                    margin: 0;
                    font-size: 24px;
                }
                h2 {
                    color: #0056b3;
                    font-size: 18px;
                    margin-top: 20px;
                    border-bottom: 1px solid #ddd;
                    padding-bottom: 5px;
                }
                .info-block {
                    margin-bottom: 20px;
                }
                .card {
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    margin-bottom: 20px;
                    background-color: #f9f9f9;
                }
                .card-header {
                    background-color: #f0f0f0;
                    padding: 10px 15px;
                    border-bottom: 1px solid #ddd;
                    font-weight: bold;
                }
                .card-body {
                    padding: 15px;
                }
                .stats {
                    display: flex;
                    flex-wrap: wrap;
                    margin: 0 -10px;
                }
                .stat-item {
                    flex: 1;
                    min-width: 120px;
                    margin: 10px;
                    padding: 15px;
                    background-color: #fff;
                    border-radius: 4px;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                    text-align: center;
                }
                .stat-value {
                    font-size: 24px;
                    font-weight: bold;
                    color: #0056b3;
                    margin-bottom: 5px;
                }
                .stat-label {
                    font-size: 14px;
                    color: #666;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                th, td {
                    padding: 8px 12px;
                    text-align: left;
                    border-bottom: 1px solid #ddd;
                }
                th {
                    background-color: #f0f0f0;
                    font-weight: bold;
                }
                tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .footer {
                    margin-top: 50px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                    border-top: 1px solid #ddd;
                    padding-top: 20px;
                }
                @media print {
                    body {
                        padding: 0;
                    }
                    .no-print {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="/assets/img/logo.png" alt="Simão Refrigeração" class="logo">
                    <h1>Simão Refrigeração</h1>
                    <p>Serviços de instalação, manutenção e projetos de ar condicionado</p>
                </div>
                
                <h1>Relatório <?= $titulo_periodo ?></h1>
                <p>Gerado em: <?= date('d/m/Y H:i') ?></p>
                
                <div class="card">
                    <div class="card-header">Resumo</div>
                    <div class="card-body">
                        <div class="stats">
                            <div class="stat-item">
                                <div class="stat-value"><?= $resumo['total_agendamentos'] ?></div>
                                <div class="stat-label">Total de Agendamentos</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= $resumo['total_concluidos'] ?></div>
                                <div class="stat-label">Concluídos</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= $resumo['total_pendentes'] + $resumo['total_confirmados'] ?></div>
                                <div class="stat-label">Em Aberto</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= $resumo['total_cancelados'] ?></div>
                                <div class="stat-label">Cancelados</div>
                            </div>
                        </div>
                        
                        <div class="stats">
                            <div class="stat-item">
                                <div class="stat-value">R$ <?= number_format($resumo['valor_total'], 2, ',', '.') ?></div>
                                <div class="stat-label">Valor Total</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">R$ <?= number_format($resumo['valor_pago_total'], 2, ',', '.') ?></div>
                                <div class="stat-label">Valor Pago</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">R$ <?= number_format($resumo['valor_total'] - $resumo['valor_pago_total'], 2, ',', '.') ?></div>
                                <div class="stat-label">Valor em Aberto</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h2>Serviços Mais Realizados</h2>
                        <?php if (!empty($servicos_populares)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Serviço</th>
                                    <th>Quantidade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicos_populares as $servico): ?>
                                <tr>
                                    <td><?= $servico['titulo'] ?></td>
                                    <td><?= $servico['total'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p>Nenhum serviço realizado no período.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <h2>Técnicos Mais Produtivos</h2>
                        <?php if (!empty($tecnicos_produtivos)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Técnico</th>
                                    <th>Serviços Realizados</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tecnicos_produtivos as $tecnico): ?>
                                <tr>
                                    <td><?= $tecnico['nome'] ?></td>
                                    <td><?= $tecnico['total'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p>Nenhum técnico com serviços realizados no período.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <h2>Clientes Mais Frequentes</h2>
                <?php if (!empty($clientes_frequentes)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Serviços Contratados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes_frequentes as $cliente): ?>
                        <tr>
                            <td><?= $cliente['nome'] ?></td>
                            <td><?= $cliente['total'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>Nenhum cliente com serviços realizados no período.</p>
                <?php endif; ?>
                
                <h2>Últimos Agendamentos Concluídos</h2>
                <?php if (!empty($ultimos_agendamentos)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Cliente</th>
                            <th>Serviço</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimos_agendamentos as $agendamento): ?>
                        <tr>
                            <td><?= str_pad($agendamento['id'], 6, '0', STR_PAD_LEFT) ?></td>
                            <td><?= date('d/m/Y', strtotime($agendamento['data_agendamento'])) ?></td>
                            <td><?= $agendamento['cliente_nome'] ?></td>
                            <td><?= $agendamento['servico_titulo'] ?></td>
                            <td>R$ <?= number_format($agendamento['valor_cobrado'], 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>Nenhum agendamento concluído no período.</p>
                <?php endif; ?>
                
                <div class="footer">
                    <p>Simão Refrigeração - CNPJ: XX.XXX.XXX/0001-XX</p>
                    <p>Endereço: Rua Exemplo, 123 - Bairro - Cidade/UF - CEP: 00000-000</p>
                    <p>Telefone: (XX) XXXX-XXXX - Email: contato@simaorefrigeracao.com.br</p>
                    <p>www.simaorefrigeracao.com.br</p>
                </div>
                
                <div class="no-print" style="margin-top: 20px; text-align: center;">
                    <button onclick="window.print();" style="padding: 10px 20px; background-color: #0056b3; color: white; border: none; border-radius: 4px; cursor: pointer;">Imprimir</button>
                </div>
            </div>
            
            <script>
                // Imprimir automaticamente
                window.onload = function() {
                    setTimeout(function() {
                        window.print();
                    }, 1000);
                };
            </script>
        </body>
        </html>
        <?php
        
        // Obter conteúdo do buffer
        $html = ob_get_clean();
        
        // Exibir HTML
        echo $html;
    } catch (PDOException $e) {
        die('Erro ao gerar PDF: ' . ($DEBUG_MODE ? $e->getMessage() : 'Erro interno'));
    }
}
