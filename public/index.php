<?php

require __DIR__ . '/../src/settings.php';

use Slim\Factory\AppFactory;
use Joabe\Buscaprecos\Controller\ProcessoController; // Importa a nova classe
use Joabe\Buscaprecos\Controller\ItemController;
use Joabe\Buscaprecos\Controller\PrecoController; 

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

// Rota para PROCESSAR a exclusão de um processo
$app->post('/processos/{id}/excluir', [ProcessoController::class, 'excluir']);


// Rota para MOSTRAR a lista de itens de um processo específico
$app->get('/processos/{id}/itens', [ItemController::class, 'listar']);

// Rota para PROCESSAR os dados do formulário e criar o novo item
$app->post('/processos/{processo_id}/itens', [ItemController::class, 'criar']);

// Rota para MOSTRAR o formulário de edição de um item
$app->get('/processos/{processo_id}/itens/{item_id}/editar', [ItemController::class, 'exibirFormularioEdicao']);

// Rota para PROCESSAR a atualização do item
$app->post('/processos/{processo_id}/itens/{item_id}/editar', [ItemController::class, 'atualizar']);

// Rota para PROCESSAR a exclusão de um item
$app->post('/processos/{processo_id}/itens/{item_id}/excluir', [ItemController::class, 'excluir']);

// --- NOVA ROTA PARA PESQUISA DE PREÇOS ---
// Rota para MOSTRAR o painel de pesquisa de preços de um item
$app->get('/processos/{processo_id}/itens/{item_id}/pesquisar', [PrecoController::class, 'exibirPainel']);

// Rota para PROCESSAR o formulário e salvar uma nova cotação de preço
$app->post('/processos/{processo_id}/itens/{item_id}/precos', [PrecoController::class, 'criar']);

$app->run();