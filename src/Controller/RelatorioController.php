<?php

namespace Joabe\Buscaprecos\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;

class RelatorioController
{
    public function gerarRelatorio($request, $response, $args)
    {
        $processo_id = $args['id'];
        $pdo = \getDbConnection();

        // 1. BUSCA DE DADOS DO PROCESSO
        $stmtProcesso = $pdo->prepare("SELECT * FROM processos WHERE id = ?");
        $stmtProcesso->execute([$processo_id]);
        $dadosProcesso = $stmtProcesso->fetch(\PDO::FETCH_ASSOC);

        // 2. BUSCA DE ITENS E PREÇOS (Consulta Otimizada)
        $sqlItens = "SELECT i.*, pc.id as preco_id, pc.fonte, pc.valor, pc.data_coleta, pc.fornecedor_nome, pc.status_analise, pc.justificativa_descarte 
                     FROM itens i 
                     LEFT JOIN precos_coletados pc ON i.id = pc.item_id 
                     WHERE i.processo_id = ? 
                     ORDER BY i.numero_item ASC, pc.valor ASC";
        $stmtItens = $pdo->prepare($sqlItens);
        $stmtItens->execute([$processo_id]);
        $resultados = $stmtItens->fetchAll(\PDO::FETCH_ASSOC);
        
        $dadosItens = [];
        foreach ($resultados as $linha) {
            $itemId = $linha['id'];
            if (!isset($dadosItens[$itemId])) {
                $dadosItens[$itemId] = $linha;
                $dadosItens[$itemId]['precos'] = [];
            }
            if ($linha['preco_id']) {
                $dadosItens[$itemId]['precos'][] = $linha;
            }
        }
        
        // 3. BUSCA DE DADOS DE SOLICITAÇÕES A FORNECEDORES
        $sqlSolicitacoes = "SELECT f.razao_social, lsf.status, ls.justificativa_fornecedores
                            FROM lotes_solicitacao ls
                            JOIN lotes_solicitacao_fornecedores lsf ON ls.id = lsf.lote_solicitacao_id
                            JOIN fornecedores f ON lsf.fornecedor_id = f.id
                            WHERE ls.processo_id = ?";
        $stmtSolicitacoes = $pdo->prepare($sqlSolicitacoes);
        $stmtSolicitacoes->execute([$processo_id]);
        $dadosSolicitacoes = $stmtSolicitacoes->fetchAll(\PDO::FETCH_ASSOC);

        // 4. PREPARA DADOS DAS FONTES CONSULTADAS
        $fontesUtilizadasBruto = [];
        foreach ($dadosItens as $item) {
            foreach ($item['precos'] as $preco) { $fontesUtilizadasBruto[] = $preco['fonte']; }
        }
        $mapaFontes = ['Painel de Preços' => 'I', 'Contratação Similar' => 'II', 'Site Especializado' => 'III', 'Pesquisa com Fornecedor' => 'IV', 'Nota Fiscal' => 'V'];
        $incisosUtilizados = [];
        foreach (array_unique($fontesUtilizadasBruto) as $fonte) {
            if (isset($mapaFontes[$fonte])) { $incisosUtilizados[] = $mapaFontes[$fonte]; }
        }
        sort($incisosUtilizados);
        $priorizouOficiais = in_array('I', $incisosUtilizados) || in_array('II', $incisosUtilizados);
        $dadosFontes = ['fontes_utilizadas' => $incisosUtilizados, 'priorizou_oficiais' => $priorizouOficiais];

        // 5. RENDERIZA O TEMPLATE HTML E GERA O PDF
        ob_start();
        require __DIR__ . '/../View/relatorios/nota_tecnica.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $dompdf->stream("Relatorio_Processo_{$processo_id}.pdf", ["Attachment" => false]);
        return $response;
    }
}