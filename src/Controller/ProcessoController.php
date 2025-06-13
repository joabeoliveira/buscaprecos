<?php

namespace Joabe\Buscaprecos\Controller;

class ProcessoController
{
    public function listar($request, $response, $args)
    {
        $pdo = \getDbConnection();
        $stmt = $pdo->query("SELECT * FROM processos ORDER BY data_criacao DESC");
        $processos = $stmt->fetchAll();

        ob_start();
        require __DIR__ . '/../View/processos/lista.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }

    // --- CORREÇÃO APLICADA AQUI ---
    public function exibirFormulario($request, $response, $args)
    {
        // Adicionamos a mesma lógica do método 'listar' para capturar o HTML
        ob_start();
        require __DIR__ . '/../View/processos/formulario.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        // A linha que faltava: retornar o objeto de resposta
        return $response;
    }
    // --- FIM DA CORREÇÃO ---

    public function criar($request, $response, $args)
    {
        $dados = $request->getParsedBody();

        $sql = "INSERT INTO processos (numero_processo, nome_processo, tipo_contratacao, status) VALUES (?, ?, ?, ?)";

        $pdo = \getDbConnection();
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $dados['numero_processo'],
            $dados['nome_processo'],
            $dados['tipo_contratacao'],
            $dados['status']
        ]);

        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }

public function exibirFormularioEdicao($request, $response, $args)
    {
        $id = $args['id'];
        $pdo = \getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
        $stmt->execute([$id]);
        $processo = $stmt->fetch();

        ob_start();
        // Passamos a variável $processo para a view
        require __DIR__ . '/../View/processos/formulario_edicao.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }

    // NOVO MÉTODO: Salva as alterações no banco de dados
    public function atualizar($request, $response, $args)
    {
        $id = $args['id'];
        $dados = $request->getParsedBody();

        $sql = "UPDATE processos SET numero_processo = ?, nome_processo = ?, tipo_contratacao = ?, status = ? WHERE id = ?";

        $pdo = \getDbConnection();
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $dados['numero_processo'],
            $dados['nome_processo'],
            $dados['tipo_contratacao'],
            $dados['status'],
            $id
        ]);

        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }
}