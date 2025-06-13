<?php

require __DIR__ . '/../src/settings.php';

use Slim\Factory\AppFactory;
use Joabe\Buscaprecos\Controller\ProcessoController; // Importa a nova classe

$app = AppFactory::create();

// Rota principal agora é o nosso dashboard
// Ela chama o método 'listar' da classe 'ProcessoController'
$app->get('/dashboard', [ProcessoController::class, 'listar']);

// Rota para MOSTRAR o formulário de criação
$app->get('/processos/novo', [ProcessoController::class, 'exibirFormulario']);

// Rota para PROCESSAR os dados do formulário enviado
$app->post('/processos', [ProcessoController::class, 'criar']);

// Rota de teste para a raiz do site
$app->get('/', function ($request, $response, $args) {
    $response->getBody()->write("Página Inicial. Acesse /dashboard para ver os processos.");
    return $response;
});

// Rota para MOSTRAR o formulário de edição com os dados do processo
$app->get('/processos/{id}/editar', [ProcessoController::class, 'exibirFormularioEdicao']);

// Rota para PROCESSAR a atualização dos dados
$app->post('/processos/{id}/editar', [ProcessoController::class, 'atualizar']);

$app->run();