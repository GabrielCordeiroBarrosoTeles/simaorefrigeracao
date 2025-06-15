<?php
// API endpoint para o sistema
require_once __DIR__ . '/../../../bootstrap-doctrine.php';

use App\Presentation\Controller\ApiController;

// Configurar cabeçalhos para API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Tratar requisições OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Obter a rota da URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/simaorefrigeracao/api/', '', $uri);
$uri = rtrim($uri, '/');

// Se não houver rota, usar 'home' como padrão
if (empty($uri)) {
    $uri = 'home';
}

// Obter o método HTTP
$metodo = $_SERVER['REQUEST_METHOD'];

// Obter os dados da requisição
$dados = [];
if ($metodo === 'POST' || $metodo === 'PUT') {
    $conteudo = file_get_contents('php://input');
    $dados = json_decode($conteudo, true) ?? [];
} elseif ($metodo === 'GET') {
    $dados = $_GET;
}

try {
    // Inicializar o controlador da API
    $entityManager = getEntityManager();
    $apiController = new ApiController($entityManager);
    
    // Processar a requisição
    $resposta = $apiController->processarRequisicao($uri, $metodo, $dados);
    
    // Definir o código de status HTTP
    http_response_code($resposta['status'] ?? 200);
    
    // Retornar a resposta como JSON
    echo json_encode($resposta);
} catch (Exception $e) {
    // Tratar erros
    http_response_code(500);
    echo json_encode([
        'status' => 500,
        'sucesso' => false,
        'erro' => 'Erro interno do servidor',
        'mensagem' => $e->getMessage()
    ]);
}