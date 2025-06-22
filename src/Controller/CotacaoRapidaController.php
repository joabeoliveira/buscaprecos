<?php

namespace Joabe\Buscaprecos\Controller;

class CotacaoRapidaController
{
    /**
     * Exibe o formulário principal da Cotação Rápida.
     */
    public function exibirFormulario($request, $response, $args)
    {
        $tituloPagina = "Cotação Rápida";
        $paginaConteudo = __DIR__ . '/../View/cotacao_rapida/formulario.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        $view = ob_get_clean();

        $response->getBody()->write($view);
        return $response;
    }

    /**
     * Busca preços nas APIs do governo (Incisos I e II) e retorna os resultados em JSON.
     */
    public function buscarPrecos($request, $response, $args)
    {
        $dados = $request->getParsedBody();
        $catmat = $dados['catmat'] ?? null;
        $regiao = $dados['regiao'] ?? null;

        if (!$catmat) {
            return $response->withJson(['erro' => 'O código CATMAT/CATSER é obrigatório.'], 400);
        }

        // Busca no Painel de Preços (Inciso I)
        $resultadosPainel = $this->buscarApiComprasGov($catmat);

        // Busca em Contratações Similares por Região (Inciso II)
        $resultadosSimilares = [];
        if ($regiao) {
            $resultadosSimilares = $this->buscarApiComprasGov($catmat, ['estado' => $regiao]);
        }

        // Une os resultados das duas fontes
        $resultadosFinais = array_merge($resultadosPainel, $resultadosSimilares);

        if (empty($resultadosFinais)) {
            return $response->withJson(['mensagem' => 'Nenhum preço encontrado para o CATMAT informado.']);
        }
        
        // Calcula as estatísticas
        $estatisticas = $this->calcularEstatisticas($resultadosFinais);

        // Retorna os dados completos
        return $response->withJson([
            'estatisticas' => $estatisticas,
            'resultados' => $resultadosFinais
        ]);
    }

    /**
     * Função auxiliar para chamar a API de Dados Abertos.
     */
    private function buscarApiComprasGov(string $catmat, array $filtros = []): array
    {
        $baseUrl = "https://dadosabertos.compras.gov.br/modulo-pesquisa-preco/1_consultarMaterial";
        $parametros = [
            'codigoItemCatalogo' => $catmat,
            'dataResultado' => 'true',
            'tamanhoPagina' => 50 // Busca um número maior de resultados
        ];

        if (!empty($filtros['estado'])) {
            $parametros['estado'] = $filtros['estado'];
        }

        $url = $baseUrl . '?' . http_build_query($parametros);

        $client = new \GuzzleHttp\Client(['verify' => false]);
        try {
            $apiResponse = $client->request('GET', $url);
            $dados = json_decode($apiResponse->getBody()->getContents(), true);
            return $dados['resultado'] ?? [];
        } catch (\Exception $e) {
            // Em caso de erro na API, retorna um array vazio para não quebrar a aplicação
            error_log("Erro na API de Cotação Rápida: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Função auxiliar para calcular estatísticas (copiada de AnaliseController).
     */
    private function calcularEstatisticas(array $precos): array
    {
        $estatisticas = ['total' => 0, 'minimo' => 0, 'maximo' => 0, 'media' => 0, 'mediana' => 0];
        if (empty($precos)) {
            return $estatisticas;
        }

        $valores = array_column($precos, 'precoUnitario');
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
}