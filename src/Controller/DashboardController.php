<?php
namespace Joabe\Buscaprecos\Controller;

class DashboardController
{
    public function exibir($request, $response, $args)
    {
        $pdo = \getDbConnection();

        // Consultas para os Gráficos
        $dadosStatus = $pdo->query("SELECT status, COUNT(*) as total FROM processos GROUP BY status")->fetchAll();
        $dadosTipo = $pdo->query("SELECT tipo_contratacao, COUNT(*) as total FROM processos GROUP BY tipo_contratacao")->fetchAll();
        $dadosAgentes = $pdo->query("SELECT agente_responsavel, COUNT(*) as total FROM processos WHERE agente_responsavel IS NOT NULL AND agente_responsavel != '' GROUP BY agente_responsavel ORDER BY total DESC LIMIT 5")->fetchAll();

        // Consultas para os Cartões de KPI (Key Performance Indicator)
        $totalProcessos = $pdo->query("SELECT COUNT(*) FROM processos")->fetchColumn();
        $totalEmAndamento = $pdo->query("SELECT COUNT(*) FROM processos WHERE status = 'Pesquisa em Andamento'")->fetchColumn();
        $totalFinalizados = $pdo->query("SELECT COUNT(*) FROM processos WHERE status = 'Finalizado'")->fetchColumn();

        // Prepara as variáveis para o layout principal
        $tituloPagina = "Dashboard";
        $paginaConteudo = __DIR__ . '/../View/dashboard.php';

        // Renderiza o layout principal, que por sua vez incluirá o nosso conteúdo
        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }
}