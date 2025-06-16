<?php

namespace Joabe\Buscaprecos\Controller;

class CotacaoPublicaController
{
    /**
     * Exibe o formulário de cotação para o fornecedor.
     */
    public function exibirFormulario($request, $response, $args)
    {
        $token = $request->getQueryParams()['token'] ?? null;
        if (!$token) {
            return $this->exibirPaginaDeErro($response, "Token de acesso não fornecido. Por favor, utilize o link enviado para o seu e-mail.");
        }

        $pdo = \getDbConnection();
        
        // Valida o token e busca as informações da solicitação
        $sql = "SELECT lsf.id as solicitacao_fornecedor_id, lsf.status, ls.prazo_final, f.razao_social, p.nome_processo
                FROM lotes_solicitacao_fornecedores lsf
                JOIN lotes_solicitacao ls ON lsf.lote_solicitacao_id = ls.id
                JOIN fornecedores f ON lsf.fornecedor_id = f.id
                JOIN processos p ON ls.processo_id = p.id
                WHERE lsf.token = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$token]);
        $solicitacao = $stmt->fetch();

        if (!$solicitacao) {
            return $this->exibirPaginaDeErro($response, "Solicitação de cotação inválida ou não encontrada.");
        }

        if ($solicitacao['status'] === 'Respondido') {
            return $this->exibirPaginaDeErro($response, "Esta cotação já foi respondida. Obrigado!");
        }

        if (new \DateTime() > new \DateTime($solicitacao['prazo_final'])) {
            // Opcional: Atualizar status para Expirado aqui
            return $this->exibirPaginaDeErro($response, "O prazo para responder a esta cotação expirou em " . date('d/m/Y', strtotime($solicitacao['prazo_final'])) . ".");
        }

        // Busca os itens associados a esta solicitação
        $sqlItens = "SELECT i.id, i.descricao, i.unidade_medida
                     FROM lotes_solicitacao_itens lsi
                     JOIN itens i ON lsi.item_id = i.id
                     JOIN lotes_solicitacao_fornecedores lsf ON lsi.lote_solicitacao_id = lsf.lote_solicitacao_id
                     WHERE lsf.token = ?";

        $stmtItens = $pdo->prepare($sqlItens);
        $stmtItens->execute([$token]);
        $itens = $stmtItens->fetchAll();
        
        $paginaConteudo = __DIR__ . '/../View/publico/formulario-cotacao.php';

        // Renderiza o layout principal, passando as variáveis necessárias
        ob_start();
        require __DIR__ . '/../View/layout/public.php'; 
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }

    /**
     * Salva a resposta de cotação do fornecedor.
     */
    public function salvarResposta($request, $response, $args)
    {
        $dados = $request->getParsedBody();
        $token = $dados['token'] ?? null;
        $precos = $dados['precos'] ?? [];

        if (!$token || empty($precos)) {
            return $this->exibirPaginaDeErro($response, "Dados inválidos. Não foi possível salvar a cotação.");
        }

        $pdo = \getDbConnection();

        // Busca o ID da solicitação e o ID do lote a partir do token
        $stmtInfo = $pdo->prepare("SELECT id, lote_solicitacao_id FROM lotes_solicitacao_fornecedores WHERE token = ? AND status = 'Enviado'");
        $stmtInfo->execute([$token]);
        $solicitacaoInfo = $stmtInfo->fetch();

        if (!$solicitacaoInfo) {
            return $this->exibirPaginaDeErro($response, "Solicitação inválida, já respondida ou expirada.");
        }
        
        $solicitacaoFornecedorId = $solicitacaoInfo['id'];
        $loteId = $solicitacaoInfo['lote_solicitacao_id'];

        try {
            $pdo->beginTransaction();

            // 1. Atualiza o status da solicitação para 'Respondido'
            $sqlStatus = "UPDATE lotes_solicitacao_fornecedores SET status = 'Respondido', data_resposta = NOW() WHERE id = ?";
            $stmtStatus = $pdo->prepare($sqlStatus);
            $stmtStatus->execute([$solicitacaoFornecedorId]);

            // 2. Insere os preços cotados na tabela precos_coletados
            $sqlPreco = "INSERT INTO precos_coletados (item_id, fonte, valor, unidade_medida, data_coleta) VALUES (?, ?, ?, ?, NOW())";
            $stmtPreco = $pdo->prepare($sqlPreco);

            foreach ($precos as $itemId => $dadosPreco) {
                if (!empty($dadosPreco['valor'])) {
                    $stmtPreco->execute([
                        $itemId,
                        'Pesquisa com Fornecedor',
                        $dadosPreco['valor'],
                        $dadosPreco['unidade_medida']
                    ]);
                }
            }
            
            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            error_log("Erro ao salvar cotação: " . $e->getMessage());
            return $this->exibirPaginaDeErro($response, "Ocorreu um erro interno ao salvar sua cotação. Por favor, tente novamente.");
        }
        
        $paginaConteudo = __DIR__ . '/../View/publico/sucesso.php';
        ob_start();
        require __DIR__ . '/../View/layout/public.php';
        $view = ob_get_clean();
        $response->getBody()->write($view);
        return $response;
    }

    /**
     * Helper para exibir uma página de erro genérica.
     */
    private function exibirPaginaDeErro($response, $mensagem) {
        $paginaConteudo = __DIR__ . '/../View/publico/erro.php';
        ob_start();
        require __DIR__ . '/../View/layout/public.php';
        $view = ob_get_clean();
        $response->getBody()->write($view);
        return $response->withStatus(400);
    }
}