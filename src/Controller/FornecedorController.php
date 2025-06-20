<?php

namespace Joabe\Buscaprecos\Controller;

class FornecedorController
{
    public function listar($request, $response, $args)
    {
        $pdo = \getDbConnection();
        $stmt = $pdo->query("SELECT * FROM fornecedores ORDER BY razao_social ASC");
        $fornecedores = $stmt->fetchAll();

        $tituloPagina = "Fornecedores Cadastrados";
        $paginaConteudo = __DIR__ . '/../View/fornecedores/lista.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }

    public function exibirFormulario($request, $response, $args)
    {
        $tituloPagina = "Novo Fornecedor";
        $paginaConteudo = __DIR__ . '/../View/fornecedores/formulario.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }

    public function criar($request, $response, $args)
    {
        $dados = $request->getParsedBody();
        
        $sql = "INSERT INTO fornecedores (razao_social, cnpj, email, endereco, telefone, ramo_atividade) VALUES (?, ?, ?, ?, ?, ?)";
    
    $pdo = \getDbConnection();
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([
            $dados['razao_social'],
            $dados['cnpj'],
            $dados['email'],
            $dados['endereco'] ?? null, // Salva o novo campo de endereço
            $dados['telefone'] ?? null,
            $dados['ramo_atividade'] ?? null
        ]);
        } catch (\PDOException $e) {
            // Tratar erro de CNPJ duplicado
            if ($e->getCode() == 23000) { 
                // Você pode implementar uma flash message de erro aqui
                die("Erro: O CNPJ informado já está cadastrado.");
            }
            throw $e;
        }

        return $response->withHeader('Location', '/fornecedores')->withStatus(302);
    }

    /* Retorna a lista de fornecedores em formato JSON para a API. */

    public function listarJson($request, $response, $args)
    {
        $pdo = \getDbConnection();
        $stmt = $pdo->query("SELECT id, razao_social, ramo_atividade FROM fornecedores ORDER BY razao_social ASC");
        $fornecedores = $stmt->fetchAll();

        return $response->withJson($fornecedores);
    }

    public function listarRamosAtividade($request, $response, $args)
    {
        $pdo = \getDbConnection();
        $stmt = $pdo->query("SELECT DISTINCT ramo_atividade FROM fornecedores WHERE ramo_atividade IS NOT NULL AND ramo_atividade != ''");
        $ramosDb = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $ramosUnicos = [];
        foreach ($ramosDb as $ramoString) {
            $ramos = array_map('trim', explode(',', $ramoString));
            $ramosUnicos = array_merge($ramosUnicos, $ramos);
        }

        $ramosUnicos = array_unique(array_filter($ramosUnicos));
        sort($ramosUnicos);

        return $response->withJson($ramosUnicos);
    }

    /**
     * Lista fornecedores, opcionalmente filtrando por ramo de atividade.
     */
    public function listarPorRamo($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $ramo = $params['ramo'] ?? 'todos';

        $pdo = \getDbConnection();
        $sql = "SELECT id, razao_social FROM fornecedores";

        if ($ramo !== 'todos') {
            $sql .= " WHERE ramo_atividade LIKE ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(["%{$ramo}%"]);
        } else {
            $stmt = $pdo->query($sql);
        }
        
        $fornecedores = $stmt->fetchAll();
        return $response->withJson($fornecedores);
    }

}