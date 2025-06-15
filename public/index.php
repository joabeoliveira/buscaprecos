<?php
session_start();
require __DIR__ . '/../src/settings.php';

use Slim\Factory\AppFactory;
use Joabe\Buscaprecos\Controller\ProcessoController;
use Joabe\Buscaprecos\Controller\ItemController;
use Joabe\Buscaprecos\Controller\PrecoController;
use Joabe\Buscaprecos\Controller\DashboardController; // <-- ADICIONADO

$app = AppFactory::create();

// Rota para o diretório raiz
$app->get('/', function ($request, $response) {
    return $response->withHeader('Location', '/dashboard')->withStatus(302);
});

// ROTA PRINCIPAL AGORA É O NOVO DASHBOARD
$app->get('/dashboard', [DashboardController::class, 'exibir']);

// ROTAS PARA PROCESSOS (AGORA EM /processos)
$app->get('/processos', [ProcessoController::class, 'listar']); // <-- NOVA ROTA PARA A LISTA
$app->get('/processos/novo', [ProcessoController::class, 'exibirFormulario']);
$app->post('/processos', [ProcessoController::class, 'criar']);
$app->get('/processos/{id}/editar', [ProcessoController::class, 'exibirFormularioEdicao']);
$app->post('/processos/{id}/editar', [ProcessoController::class, 'atualizar']);
$app->post('/processos/{id}/excluir', [ProcessoController::class, 'excluir']);

// ROTAS PARA ITENS (sem alteração)
$app->get('/processos/{processo_id}/itens', [ItemController::class, 'listar']);
$app->post('/processos/{processo_id}/itens', [ItemController::class, 'criar']);
$app->post('/processos/{processo_id}/itens/{item_id}/excluir', [ItemController::class, 'excluir']);
$app->get('/processos/{processo_id}/itens/{item_id}/editar', [ItemController::class, 'exibirFormularioEdicao']);
$app->post('/processos/{processo_id}/itens/{item_id}/editar', [ItemController::class, 'atualizar']);

// ROTAS PARA PREÇOS (sem alteração)
$app->get('/processos/{processo_id}/itens/{item_id}/pesquisar', [PrecoController::class, 'exibirPainel']);
$app->post('/api/painel-de-precos', [PrecoController::class, 'buscarPainelDePrecos']);
$app->post('/processos/{processo_id}/itens/{item_id}/precos', [PrecoController::class, 'criar']);


$app->run();