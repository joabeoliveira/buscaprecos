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
        $stmtItem = $pdo->prepare("SELECT * FROM itens WHERE id = ?");
        $stmtItem->execute([$item_id]);
        $item = $stmtItem->fetch();

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
            $dados['fornecedor_nome'] ?: null, // Usa null se o campo estiver vazio
            $dados['fornecedor_cnpj'] ?: null, // Usa null se o campo estiver vazio
            $dados['link_evidencia'] ?: null   // Usa null se o campo estiver vazio
        ]);

        // Redireciona de volta para o painel de pesquisa para ver o preço na lista
        $redirectUrl = "/processos/{processo_id}/itens/{item_id}/pesquisar";
        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }
}