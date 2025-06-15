<?php
namespace Joabe\Buscaprecos\Controller;

class PrecoController
{
    public function exibirPainel($request, $response, $args)
{
    $processo_id = $args['processo_id'];
    $item_id = $args['item_id'];
    $pdo = \getDbConnection();

    // 1. Busca os dados do processo "pai"
    $stmtProcesso = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
    $stmtProcesso->execute([$processo_id]);
    $processo = $stmtProcesso->fetch();

    // 2. Busca o item específico que estamos pesquisando
    $stmtItem = $pdo->prepare("SELECT * FROM itens WHERE id = ? AND processo_id = ?");
    $stmtItem->execute([$item_id, $processo_id]);
    $item = $stmtItem->fetch();

    // 3. Verifica se o processo e o item foram encontrados
    if (!$processo || !$item) {
        $response->getBody()->write("Erro 404: Processo ou Item não encontrado.");
        return $response->withStatus(404);
    }

    // 4. Busca os preços na tabela correta: 'precos_coletados'
    $stmtPrecos = $pdo->prepare("SELECT * FROM precos_coletados WHERE item_id = ? ORDER BY criado_em DESC");
    $stmtPrecos->execute([$item_id]);
    $precos = $stmtPrecos->fetchAll();

    // 5. Prepara as variáveis para o layout principal
    $tituloPagina = "Painel de Pesquisa de Preços";
    $paginaConteudo = __DIR__ . '/../View/precos/painel.php';

    // 6. Renderiza o layout principal
    ob_start();
    require __DIR__ . '/../View/layout/main.php';
    $view = ob_get_clean();
    
    $response->getBody()->write($view);
    return $response;
}

    // NOVO MÉTODO: Salva uma nova cotação de preço no banco
    public function criar($request, $response, $args)
{
    $processo_id = $args['processo_id'];
    $item_id = $args['item_id'];
    $dados = $request->getParsedBody();

    // Query SQL atualizada para incluir a nova coluna
    $sql = "INSERT INTO precos_coletados 
                (item_id, fonte, valor, unidade_medida, data_coleta, fornecedor_nome, fornecedor_cnpj, link_evidencia) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $pdo = \getDbConnection();
    $stmt = $pdo->prepare($sql);
    
    // Array de execução atualizado com o novo campo
    $stmt->execute([
        $item_id,
        $dados['fonte'],
        $dados['valor'],
        $dados['unidade_medida'], // Novo campo adicionado
        $dados['data_coleta'],
        $dados['fornecedor_nome'] ?: null,
        $dados['fornecedor_cnpj'] ?: null,
        $dados['link_evidencia'] ?: null
    ]);

    $redirectUrl = "/processos/{$processo_id}/itens/{$item_id}/pesquisar";

    return $response->withHeader('Location', $redirectUrl)->withStatus(302);
}

    public function buscarPainelDePrecos($request, $response, $args)
{
    $dados = $request->getParsedBody();
    $catmat = $dados['catmat'] ?? null;

    if (!$catmat) {
        $response->getBody()->write(json_encode(['erro' => 'CATMAT não fornecido']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // --- CORREÇÃO APLICADA AQUI ---
    // Monta a URL completa e correta da API do governo, incluindo todos os parâmetros.
    $url = "https://dadosabertos.compras.gov.br/modulo-pesquisa-preco/1_consultarMaterial?pagina=1&tamanhoPagina=10&codigoItemCatalogo={$catmat}&dataResultado=true";

    $client = new \GuzzleHttp\Client([
        'verify' => false // Adicionado para ignorar problemas de certificado SSL que podem ocorrer em ambientes locais
    ]);

    try {
        $apiResponse = $client->request('GET', $url);
        $body = $apiResponse->getBody()->getContents();
        
        $response->getBody()->write($body);
        return $response->withHeader('Content-Type', 'application/json');

    } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        // Log do erro para depuração
        error_log($e->getMessage()); 
        
        $response->getBody()->write(json_encode(['erro' => 'Falha ao consultar a API do Painel de Preços.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
}

    public function excluir($request, $response, $args)
    {
        $processo_id = $args['processo_id'];
        $item_id = $args['item_id'];
        $preco_id = $args['preco_id'];

        $pdo = \getDbConnection();
        
        // Prepara e executa a query de exclusão
        $sql = "DELETE FROM precos_coletados WHERE id = ? AND item_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$preco_id, $item_id]);

        // Redireciona o usuário de volta para a página de pesquisa
        $redirectUrl = "/processos/{$processo_id}/itens/{$item_id}/pesquisar";
        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }

    /**
     * Cria múltiplas cotações de preço de uma vez (em lote).
     */
    public function criarLote($request, $response, $args)
    {
        $item_id = $args['item_id'];
        $precos = $request->getParsedBody(); // Recebe o array de preços do frontend

        if (empty($precos) || !is_array($precos)) {
            return $response->withJson(['status' => 'error', 'message' => 'Nenhum preço fornecido.'], 400);
        }

        $pdo = \getDbConnection();
        
        $sql = "INSERT INTO precos_coletados 
                    (item_id, fonte, valor, unidade_medida, data_coleta, fornecedor_nome, fornecedor_cnpj, link_evidencia) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);

        try {
            $pdo->beginTransaction();
            foreach ($precos as $preco) {
                $stmt->execute([
                    $item_id,
                    $preco['fonte'],
                    $preco['valor'],
                    $preco['unidade_medida'],
                    $preco['data_coleta'],
                    $preco['fornecedor_nome'] ?: null,
                    $preco['fornecedor_cnpj'] ?: null,
                    $preco['link_evidencia'] ?: null
                ]);
            }
            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            // Em um app real, logaríamos o erro $e->getMessage()
            return $response->withJson(['status' => 'error', 'message' => 'Falha ao salvar os preços.'], 500);
        }

        return $response->withJson(['status' => 'success', 'message' => 'Cotações salvas com sucesso.']);
    }

    /**
     * Busca contratações similares na API de dados abertos,
     * seja por região (automático) ou por UASGs específicas.
     */
    public function pesquisarContratacoesSimilares($request, $response, $args)
    {
        $item_id = $args['item_id'];
        $dadosCorpo = $request->getParsedBody();
        $uasgsSugeridas = $dadosCorpo['uasgs'] ?? [];

        $pdo = \getDbConnection();
        
        // Busca o item e seu processo pai para obter o CATMAT e a Região
        $stmtItem = $pdo->prepare("SELECT i.catmat_catser, p.regiao FROM itens i JOIN processos p ON i.processo_id = p.id WHERE i.id = ?");
        $stmtItem->execute([$item_id]);
        $itemInfo = $stmtItem->fetch();

        if (!$itemInfo || empty($itemInfo['catmat_catser'])) {
            return $response->withJson(['erro' => 'Item, CATMAT ou Região não encontrados.'], 404);
        }

        $catmat = $itemInfo['catmat_catser'];
        $regiao = $itemInfo['regiao'];
        
        $client = new \GuzzleHttp\Client(['verify' => false]);
        $resultadosFinais = ['resultado' => []];

        try {
            if (!empty($uasgsSugeridas)) {
                // Modo 1: Busca por UASGs específicas fornecidas pelo usuário
                foreach ($uasgsSugeridas as $uasg) {
                    if (empty($uasg)) continue;
                    $url = "https://dadosabertos.compras.gov.br/modulo-pesquisa-preco/1_consultarMaterial?codigoItemCatalogo={$catmat}&codigoUasg={$uasg}&dataResultado=true&tamanhoPagina=10";
                    $apiResponse = $client->request('GET', $url);
                    $dados = json_decode($apiResponse->getBody()->getContents(), true);
                    if (!empty($dados['resultado'])) {
                        $resultadosFinais['resultado'] = array_merge($resultadosFinais['resultado'], $dados['resultado']);
                    }
                }
            } else {
                // Modo 2: Busca automática pela região do processo
                $url = "https://dadosabertos.compras.gov.br/modulo-pesquisa-preco/1_consultarMaterial?codigoItemCatalogo={$catmat}&estado={$regiao}&dataResultado=true&tamanhoPagina=20";
                $apiResponse = $client->request('GET', $url);
                $dados = json_decode($apiResponse->getBody()->getContents(), true);
                if (!empty($dados['resultado'])) {
                    $resultadosFinais['resultado'] = $dados['resultado'];
                }
            }
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            error_log("Erro na API de Contratos Similares: " . $e->getMessage());
            return $response->withJson(['erro' => 'Falha ao consultar a API externa.'], 502);
        }

        return $response->withJson($resultadosFinais);
    }

}