<?php

namespace Joabe\Buscaprecos\Controller;

class AnaliseController
{
    /**
     * Exibe a "Mesa de Análise" para todos os itens de um processo.
     */
    public function exibirAnaliseProcesso($request, $response, $args)
    {
        $processo_id = $args['processo_id'];
        $pdo = \getDbConnection();

        // 1. Busca os dados do processo
        $stmtProcesso = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
        $stmtProcesso->execute([$processo_id]);
        $processo = $stmtProcesso->fetch();

        // 2. Busca todos os itens do processo
        $stmtItens = $pdo->prepare("SELECT * FROM itens WHERE processo_id = ? ORDER BY numero_item ASC");
        $stmtItens->execute([$processo_id]);
        $itens = $stmtItens->fetchAll();

        // 3. Para cada item, busca seus preços e calcula as estatísticas
        $itensComAnalise = [];
        foreach ($itens as $item) {
            $stmtPrecos = $pdo->prepare("SELECT * FROM precos_coletados WHERE item_id = ? ORDER BY valor ASC");
            $stmtPrecos->execute([$item['id']]);
            $precos = $stmtPrecos->fetchAll();

            $precosConsiderados = array_filter($precos, fn($p) => $p['status_analise'] === 'considerado');
            $estatisticas = $this->calcularEstatisticas($precosConsiderados);

            // --- LÓGICA DO ALERTA ---
            $alertaAmostraInsuficiente = ($estatisticas['total'] > 0 && $estatisticas['total'] < 3);

            $itensComAnalise[] = [
                'item' => $item,
                'precos' => $precos,
                'estatisticas' => $estatisticas,
                'alerta_amostra' => $alertaAmostraInsuficiente // Passa a flag para a view
            ];

            $itensComAnalise[] = [
                'item' => $item,
                'precos' => $precos,
                'estatisticas' => $estatisticas
            ];
        }

        $tituloPagina = "Mesa de Análise Geral do Processo";
        $paginaConteudo = __DIR__ . '/../View/analise/processo.php'; // Nova view
        
        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }

    /**
     * Função auxiliar para calcular as estatísticas de um conjunto de preços.
     */
    private function calcularEstatisticas(array $precos): array
    {
        $estatisticas = ['total' => 0, 'minimo' => 0, 'maximo' => 0, 'media' => 0, 'mediana' => 0];
        if (empty($precos)) {
            return $estatisticas;
        }

        $valores = array_column($precos, 'valor');
        sort($valores);
        $count = count($valores);
        
        $estatisticas['total'] = $count;
        $estatisticas['minimo'] = $valores[0];
        $estatisticas['maximo'] = $valores[$count - 1];
        $estatisticas['media'] = array_sum($valores) / $count;
        
        $meio = floor(($count - 1) / 2);
        if ($count % 2) {
            $estatisticas['mediana'] = $valores[$meio];
        } else {
            $estatisticas['mediana'] = ($valores[$meio] + $valores[$meio + 1]) / 2.0;
        }

        return $estatisticas;
    }


// Em src/Controller/AnaliseController.php

public function salvarAnaliseItem($request, $response, $args)
{
    $processo_id = $args['processo_id'];
    $item_id = $args['item_id'];
    $dados = $request->getParsedBody();
    $pdo = \getDbConnection();
    
    $metodologia = $dados['metodologia_estimativa'];
    $justificativa = $dados['justificativa_estimativa'];
    $justificativaExcepcionalidade = $dados['justificativa_excepcionalidade'] ?? null;
    $valorEstimado = 0;
    $redirectUrl = "/processos/{$processo_id}/analise";

    // Se a metodologia for manual, usa o valor do campo de texto.
    // Caso contrário, recalcula o valor com base nos preços "considerados".
    if ($metodologia === 'Manual') {
        $valorEstimado = (float)($dados['valor_manual'] ?? 0);
    } else {
        $stmt = $pdo->prepare("SELECT valor FROM precos_coletados WHERE item_id = ? AND status_analise = 'considerado'");
        $stmt->execute([$item_id]);
        $precosConsiderados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $estatisticas = $this->calcularEstatisticas($precosConsiderados);
        
        switch ($metodologia) {
            case 'Média':
                $valorEstimado = $estatisticas['media'];
                break;
            case 'Mediana':
                $valorEstimado = $estatisticas['mediana'];
                break;
            case 'Menor Valor':
                $valorEstimado = $estatisticas['minimo'];
                break;
        }
    }

    // =======================================================
    // INÍCIO DA CORREÇÃO: TRAVA DE SEGURANÇA DO PAINEL DE PREÇOS (Art. 6º, § 6º)
    // =======================================================
    $stmtFontes = $pdo->prepare("SELECT DISTINCT fonte FROM precos_coletados WHERE item_id = ? AND status_analise = 'considerado'");
    $stmtFontes->execute([$item_id]);
    $fontesUsadas = $stmtFontes->fetchAll(\PDO::FETCH_COLUMN);

    if (count($fontesUsadas) === 1 && $fontesUsadas[0] === 'Painel de Preços') {
        $stmtPrecosPainel = $pdo->prepare("SELECT valor FROM precos_coletados WHERE item_id = ? AND status_analise = 'considerado'");
        $stmtPrecosPainel->execute([$item_id]);
        $precosPainel = $stmtPrecosPainel->fetchAll(\PDO::FETCH_ASSOC);
        
        $estatisticasPainel = $this->calcularEstatisticas($precosPainel);
        $medianaPainel = $estatisticasPainel['mediana'];

        if ($valorEstimado > $medianaPainel) {
            $_SESSION['flash'] = [
                'tipo' => 'danger', 
                'mensagem' => 'Ajuste Bloqueado: Conforme a IN 65/2021, quando apenas preços do Painel são usados, o valor estimado (R$ ' . number_format($valorEstimado, 2, ',', '.') . ') não pode ser superior à mediana (R$ ' . number_format($medianaPainel, 2, ',', '.') . ').'
            ];
            return $response->withHeader('Location', $redirectUrl)->withStatus(302);
        }
    }
    // =======================================================
    // FIM DA CORREÇÃO
    // =======================================================
    
    // Salva os dados no banco, incluindo a nova justificativa de excepcionalidade
    $sql = "UPDATE itens 
            SET valor_estimado = ?, 
                metodologia_estimativa = ?, 
                justificativa_estimativa = ?, 
                justificativa_excepcionalidade = ? 
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$valorEstimado, $metodologia, $justificativa, $justificativaExcepcionalidade, $item_id]);

    $_SESSION['flash'] = [
        'tipo' => 'success',
        'mensagem' => 'Análise do item salva com sucesso!'
    ];

    // Redireciona de volta para a página de análise
    return $response->withHeader('Location', $redirectUrl)->withStatus(302);
}

}