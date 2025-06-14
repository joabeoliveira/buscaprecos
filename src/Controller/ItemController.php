<?php

namespace Joabe\Buscaprecos\Controller;

class ItemController
{
    public function listar($request, $response, $args)
    {
        $processo_id = $args['id'];
        $pdo = \getDbConnection();

        // 1. Busca os dados do processo "pai" para mostrar o título da página
        $stmtProcesso = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
        $stmtProcesso->execute([$processo_id]);
        $processo = $stmtProcesso->fetch();

        // Se o processo não for encontrado, podemos tratar o erro depois.

        // 2. Busca todos os itens que pertencem a este processo
        $stmtItens = $pdo->prepare("SELECT * FROM itens WHERE processo_id = ? ORDER BY numero_item ASC");
        $stmtItens->execute([$processo_id]);
        $itens = $stmtItens->fetchAll();

        // 3. Renderiza a view, passando as variáveis $processo e $itens para ela
        ob_start();
        require __DIR__ . '/../View/itens/lista.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }

    // NOVO MÉTODO: Salva o novo item no banco de dados
    public function criar($request, $response, $args)
{
    $processo_id = $args['processo_id'];
    $dados = $request->getParsedBody();

    $pdo = \getDbConnection();

    // --- INÍCIO DA VALIDAÇÃO ANTI-DUPLICIDADE ---
    // Ignora a verificação de duplicidade de CATMAT se ele estiver vazio
    $catmat = !empty($dados['catmat_catser']) ? $dados['catmat_catser'] : null;

    $sqlVerifica = "SELECT COUNT(*) FROM itens WHERE processo_id = ? AND (numero_item = ? OR catmat_catser = ?)";
    $stmtVerifica = $pdo->prepare($sqlVerifica);
    $stmtVerifica->execute([$processo_id, $dados['numero_item'], $catmat]);
    $count = $stmtVerifica->fetchColumn();

    if ($count > 0) {
        // Se encontrou duplicado, redireciona de volta com uma mensagem de erro
        $redirectUrl = "/processos/{$processo_id}/itens?erro=duplicado";
        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }
    // --- FIM DA VALIDAÇÃO ---

    // Se passou na validação, continua com o INSERT
    $sql = "INSERT INTO itens (processo_id, numero_item, catmat_catser, descricao, unidade_medida, quantidade) VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $processo_id,
        $dados['numero_item'],
        $catmat, // Usa a variável tratada
        $dados['descricao'],
        $dados['unidade_medida'],
        $dados['quantidade']
    ]); 
}

    public function exibirFormularioEdicao($request, $response, $args)
    {
        $processo_id = $args['processo_id'];
        $item_id = $args['item_id'];
        $pdo = \getDbConnection();

        // Busca o processo pai (para o título e links)
        $stmtProcesso = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
        $stmtProcesso->execute([$processo_id]);
        $processo = $stmtProcesso->fetch();

        // Busca o item específico que será editado
        $stmtItem = $pdo->prepare("SELECT * FROM itens WHERE id = ? AND processo_id = ?");
        $stmtItem->execute([$item_id, $processo_id]);
        $item = $stmtItem->fetch();

        ob_start();
        require __DIR__ . '/../View/itens/formulario_edicao.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }

    // NOVO MÉTODO: Recebe os dados do formulário e atualiza o item no banco
    public function atualizar($request, $response, $args)
{
    $processo_id = $args['processo_id'];
    $item_id = $args['item_id'];
    $dados = $request->getParsedBody();
    $pdo = \getDbConnection();

    // --- INÍCIO DA VALIDAÇÃO ANTI-DUPLICIDADE NA EDIÇÃO ---
    $catmat = !empty($dados['catmat_catser']) ? $dados['catmat_catser'] : null;

    // A query agora inclui "AND id != ?" para ignorar o próprio item na verificação
    // e também verifica se o catmat não está vazio antes de comparar.
    $sqlVerifica = "SELECT COUNT(*) FROM itens WHERE processo_id = ? AND (numero_item = ? OR (catmat_catser IS NOT NULL AND catmat_catser = ?)) AND id != ?";
    $stmtVerifica = $pdo->prepare($sqlVerifica);
    $stmtVerifica->execute([$processo_id, $dados['numero_item'], $catmat, $item_id]);
    $count = $stmtVerifica->fetchColumn();

    if ($count > 0) {
        // Se encontrou duplicado, redireciona de volta com uma mensagem de erro
        $redirectUrl = "/processos/{$processo_id}/itens?erro=duplicado";
        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }
    // --- FIM DA VALIDAÇÃO ---

    // Se passou na validação, continua com o UPDATE
    $sql = "UPDATE itens SET 
                numero_item = ?, 
                catmat_catser = ?, 
                descricao = ?, 
                unidade_medida = ?, 
                quantidade = ? 
            WHERE id = ? AND processo_id = ?";
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        $dados['numero_item'],
        $catmat, // Usa a variável tratada
        $dados['descricao'],
        $dados['unidade_medida'],
        $dados['quantidade'],
        $item_id,
        $processo_id
    ]);
    // Redireciona de volta para a lista de itens do processo
    return $response->withHeader('Location', "/processos/{$processo_id}/itens")->withStatus(302);

}

    // NOVO MÉTODO: Processa a exclusão de um item
    public function excluir($request, $response, $args)
{
    $processo_id = $args['processo_id'];
    $item_id = $args['item_id'];
    $pdo = \getDbConnection();

    // Prepara e executa a query de exclusão
    $sql = "DELETE FROM itens WHERE id = ? AND processo_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$item_id, $processo_id]);

    // Redireciona de volta para a lista de itens do processo
    return $response->withHeader('Location', "/processos/{$processo_id}/itens")->withStatus(302);

}
}