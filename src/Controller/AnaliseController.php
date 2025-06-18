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

    public function salvarAnaliseItem($request, $response, $args)
    {
        $processo_id = $args['processo_id'];
        $item_id = $args['item_id'];
        $dados = $request->getParsedBody();
        
        $metodologia = $dados['metodologia_estimativa'];
        $justificativa = $dados['justificativa_estimativa'];
        $valorEstimado = 0;

        // Se a metodologia for manual, usa o valor do campo de texto.
        // Caso contrário, recalcula o valor com base nos preços "considerados".
        if ($metodologia === 'Manual') {
            $valorEstimado = (float)($dados['valor_manual'] ?? 0);
        } else {
            $pdo = \getDbConnection();
            $stmt = $pdo->prepare("SELECT valor FROM precos_coletados WHERE item_id = ? AND status_analise = 'considerado'");
            $stmt->execute([$item_id]);
            $precosConsiderados = $stmt->fetchAll();
            
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
        
        // Salva os dados no banco
        $sql = "UPDATE itens 
                SET valor_estimado = ?, metodologia_estimativa = ?, justificativa_estimativa = ? 
                WHERE id = ?";
        
        $pdo = \getDbConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$valorEstimado, $metodologia, $justificativa, $item_id]);

        // Redireciona de volta para a página de análise
        return $response->withHeader('Location', "/processos/{$processo_id}/analise")->withStatus(302);
    }

}