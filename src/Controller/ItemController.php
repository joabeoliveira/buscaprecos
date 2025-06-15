<?php

namespace Joabe\Buscaprecos\Controller;

class ItemController
{
    public function listar($request, $response, $args)
    {
        $processo_id = $args['processo_id'];
        $pdo = \getDbConnection();

        $stmtProcesso = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
        $stmtProcesso->execute([$processo_id]);
        $processo = $stmtProcesso->fetch();

        if (!$processo) {
            $response->getBody()->write("Processo não encontrado.");
            return $response->withStatus(404);
        }

        $stmtItens = $pdo->prepare("SELECT * FROM itens WHERE processo_id = ? ORDER BY numero_item ASC");
        $stmtItens->execute([$processo_id]);
        $itens = $stmtItens->fetchAll();

        // Prepara as variáveis e chama o layout principal
        $tituloPagina = "Itens do Processo";
        $paginaConteudo = __DIR__ . '/../View/itens/lista.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
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
    $redirectUrl = "/processos/{$processo_id}/itens"; // URL de redirecionamento padrão

    // Validação de duplicidade
    $catmat = !empty($dados['catmat_catser']) ? $dados['catmat_catser'] : null;
    $sqlVerifica = "SELECT COUNT(*) FROM itens WHERE processo_id = ? AND (numero_item = ? OR (catmat_catser IS NOT NULL AND catmat_catser != '' AND catmat_catser = ?))";
    $stmtVerifica = $pdo->prepare($sqlVerifica);
    $stmtVerifica->execute([$processo_id, $dados['numero_item'], $catmat]);

    if ($stmtVerifica->fetchColumn() > 0) {
        // ERRO: Item duplicado. Salva a mensagem de erro e os dados do formulário na sessão.
        $_SESSION['flash'] = [
            'tipo' => 'danger',
            'mensagem' => 'Erro: Já existe um item com este Número ou CATMAT.',
            'dados_formulario' => $dados
        ];
        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }

    // Se passou na validação, executa o INSERT
    $sql = "INSERT INTO itens (processo_id, numero_item, catmat_catser, descricao, unidade_medida, quantidade) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $processo_id, $dados['numero_item'], $catmat,
        $dados['descricao'], $dados['unidade_medida'],
        $dados['quantidade']
    ]);

    // SUCESSO: Salva a mensagem de sucesso na sessão.
    $_SESSION['flash'] = [
        'tipo' => 'success',
        'mensagem' => 'Item adicionado com sucesso!'
    ];

    return $response->withHeader('Location', $redirectUrl)->withStatus(302);
}
    public function exibirFormularioEdicao($request, $response, $args)
    {
        $processo_id = $args['processo_id'];
    $item_id = $args['item_id'];
    $pdo = \getDbConnection();

    // Busca o processo pai
    $stmtProcesso = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
    $stmtProcesso->execute([$processo_id]);
    $processo = $stmtProcesso->fetch();

    // Busca o item específico
    $stmtItem = $pdo->prepare("SELECT * FROM itens WHERE id = ? AND processo_id = ?");
    $stmtItem->execute([$item_id, $processo_id]);
    $item = $stmtItem->fetch();

    if (!$processo || !$item) {
        $response->getBody()->write("Processo ou item não encontrado.");
        return $response->withStatus(404);
    }

    // Prepara as variáveis e chama o layout principal
    $tituloPagina = "Editar Item";
    $paginaConteudo = __DIR__ . '/../View/itens/formulario_edicao.php';

    ob_start();
    require __DIR__ . '/../View/layout/main.php';
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