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

    // --- INÍCIO DA CORREÇÃO ---
    // Verifica se o processo e o item foram encontrados no banco de dados.
    // A função fetch() do PDO retorna 'false' se não encontrar nada.
    if (!$processo || !$item) {
        // Se não encontrou, retorna uma página de erro "Não Encontrado" em vez de quebrar a aplicação.
        $response->getBody()->write("Erro 404: Processo ou Item não encontrado.");
        return $response->withStatus(404);
    }
    // --- FIM DA CORREÇÃO ---

    // 3. Busca os preços que já foram coletados para este item
    $stmtPrecos = $pdo->prepare("SELECT * FROM precos_coletados WHERE item_id = ? ORDER BY criado_em DESC");
    $stmtPrecos->execute([$item_id]);
    $precos = $stmtPrecos->fetchAll();

    // Renderiza a view, passando todas as informações
    ob_start();
    require __DIR__ . '/../View/precos/painel.php';
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

    $sql = "INSERT INTO precos_coletados 
                (item_id, fonte, valor, data_coleta, fornecedor_nome, fornecedor_cnpj, link_evidencia) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $pdo = \getDbConnection();
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        $item_id,
        $dados['fonte'],
        $dados['valor'],
        $dados['data_coleta'],
        $dados['fornecedor_nome'] ?: null,
        $dados['fornecedor_cnpj'] ?: null,
        $dados['link_evidencia'] ?: null
    ]);

    // --- A CORREÇÃO ESTÁ AQUI ---
    // A string DEVE usar aspas duplas (") para que as variáveis {$processo_id} e {$item_id} sejam interpretadas.
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
}