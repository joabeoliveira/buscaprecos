<?php
session_start();
require __DIR__ . '/../src/settings.php';

use Slim\Factory\AppFactory;
use Joabe\Buscaprecos\Controller\ProcessoController;
use Joabe\Buscaprecos\Controller\ItemController;
use Joabe\Buscaprecos\Controller\PrecoController;
use Joabe\Buscaprecos\Controller\DashboardController; // <-- ADICIONADO
use Joabe\Buscaprecos\Controller\FornecedorController; // <-- ADICIONADO
use Joabe\Buscaprecos\Controller\AnaliseController;
use Joabe\Buscaprecos\Controller\AcompanhamentoController;

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

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

// ROTA PARA EXCLUIR PREÇO (sem alteração)
$app->post('/processos/{processo_id}/itens/{item_id}/precos/{preco_id}/excluir', [PrecoController::class, 'excluir']);

$app->post('/api/processos/{processo_id}/itens/{item_id}/precos/lote', [PrecoController::class, 'criarLote']);

//   ROTA PARA BUSCA EM ÓRGÃOS
$app->post('/api/processos/{processo_id}/itens/{item_id}/pesquisar-orgaos', [PrecoController::class, 'pesquisarContratacoesSimilares']);


//         ROTAS PARA FORNECEDORES
$app->get('/fornecedores', [FornecedorController::class, 'listar']);
$app->get('/fornecedores/novo', [FornecedorController::class, 'exibirFormulario']);
$app->post('/fornecedores', [FornecedorController::class, 'criar']);

$app->get('/api/fornecedores', [FornecedorController::class, 'listarJson']);

// API para enviar as solicitações de cotação
$app->post('/api/processos/{processo_id}/itens/{item_id}/solicitar-cotacao', [PrecoController::class, 'enviarSolicitacoes']);
// ===============================================

// Rota para buscar os ramos de atividade únicos
$app->get('/api/fornecedores/ramos-atividade', [FornecedorController::class, 'listarRamosAtividade']);
// Rota para buscar fornecedores filtrando por ramo
$app->get('/api/fornecedores/por-ramo', [FornecedorController::class, 'listarPorRamo']);
// Rota principal para criar e enviar a solicitação em lote
$app->post('/api/processos/{processo_id}/solicitacao-lote', [PrecoController::class, 'enviarSolicitacaoLote']);

//   ROTA PÚBLICA PARA RESPOSTA DO FORNECEDOR
// =======================================================
$app->get('/cotacao/responder', [\Joabe\Buscaprecos\Controller\CotacaoPublicaController::class, 'exibirFormulario']);
$app->post('/cotacao/responder', [\Joabe\Buscaprecos\Controller\CotacaoPublicaController::class, 'salvarResposta']);
// =======================================================

// ROTA DA ANÁLISE GERAL DO PROCESSO
// ===============================================
$app->get('/processos/{processo_id}/analise', [AnaliseController::class, 'exibirAnaliseProcesso']);
// ===============================================

// ROTAS DE CURADORIA
// ===============================================
$app->post('/processos/{processo_id}/itens/{item_id}/precos/{preco_id}/desconsiderar', [PrecoController::class, 'desconsiderarPreco']);
$app->post('/processos/{processo_id}/itens/{item_id}/precos/{preco_id}/reconsiderar', [PrecoController::class, 'reconsiderarPreco']);
// ===============================================

// ROTA PARA SALVAR A ANÁLISE
// ===============================================
$app->post('/processos/{processo_id}/itens/{item_id}/salvar-analise', [AnaliseController::class, 'salvarAnaliseItem']);
// ===============================================

// ROTA PARA A NOVA PÁGINA DE ACOMPANHAMENTO
$app->get('/acompanhamento', [AcompanhamentoController::class, 'exibir']);

// ROTA PARA DOWNLOAD DO ANEXO (coloque perto das outras rotas)
$app->get('/download-proposta/{nome_arquivo}', function ($request, $response, $args) {
    $nomeArquivo = $args['nome_arquivo'];
    $caminhoCompleto = __DIR__ . '/../storage/propostas/' . $nomeArquivo;

    if (!file_exists($caminhoCompleto) || !preg_match('/^[a-f0-9]+\.pdf$/', $nomeArquivo)) {
        $response->getBody()->write('Arquivo não encontrado ou inválido.');
        return $response->withStatus(404);
    }

    $response = $response->withHeader('Content-Type', 'application/pdf');
    $response = $response->withHeader('Content-Disposition', 'inline; filename="' . $nomeArquivo . '"');
    $response->getBody()->write(file_get_contents($caminhoCompleto));
    return $response;
});

$app->run();