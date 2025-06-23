<?php
// Pega o caminho da URL atual para sabermos qual menu deve ficar ativo
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloPagina ?? 'Busca Preços AI' ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/catmat-search/style.css">
    <link rel="stylesheet" href="/css/dashboard.css">

    <style>
        body {
            overflow-x: hidden;
        }
        #sidebar {
            min-height: 100vh;
        }
        .main-content {
            width: 100%;
            padding: 2rem;
            overflow-y: auto;
            height: 100vh;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px;" id="sidebar">
            <a href="/dashboard" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <i class="bi bi-graph-up-arrow me-2 fs-4"></i>
                <span class="fs-4">Busca Preços AI</span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="/dashboard" class="nav-link <?= str_starts_with($currentPath, '/dashboard') || $currentPath == '/' ? 'active' : 'text-white' ?>">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="/processos" class="nav-link <?= str_starts_with($currentPath, '/processos') ? 'active' : 'text-white' ?>">
                        <i class="bi bi-folder2-open me-2"></i> Processos
                    </a>
                </li>

                
                <li>
                    <a href="/fornecedores" class="nav-link <?= str_starts_with($currentPath, '/fornecedores') ? 'active' : 'text-white' ?>">
                        <i class="bi bi-truck me-2"></i> Fornecedores
                    </a>
                </li>

                <li>
                    <a href="/acompanhamento" class="nav-link <?= str_starts_with($currentPath, '/acompanhamento') ? 'active' : 'text-white' ?>">
                        <i class="bi bi-stopwatch me-2"></i> Acompanhamento
                    </a>
                </li>

                <li>
                    <a href="/cotacao-rapida" class="nav-link <?= str_starts_with($currentPath, '/cotacao-rapida') ? 'active' : 'text-white' ?>">
                        <i class="bi bi-lightning-charge-fill me-2"></i> Cotação Rápida
                    </a>
                </li>

                <li>
                    <a href="/relatorios" class="nav-link <?= str_starts_with($currentPath, '/relatorios') ? 'active' : 'text-white' ?>">
                        <i class="bi bi-collection-fill me-2"></i> Histórico de Relatórios
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link text-white">
                        <i class="bi bi-people me-2"></i> Usuários
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link text-white">
                        <i class="bi bi-gear me-2"></i> Configurações
                    </a>
                </li>
            </ul>
            <hr>
            <div>
                <a href="#" class="d-flex align-items-center text-white text-decoration-none">
                    <i class="bi bi-person-circle fs-4 me-2"></i>
                    <strong>Usuário</strong>
                </a>
            </div>
        </div>
        <main class="main-content">
            <?php 
                if (isset($paginaConteudo) && file_exists($paginaConteudo)) {
                    include $paginaConteudo;
                } else {
                    echo "<h1>Erro: Conteúdo da página não encontrado.</h1>";
                }
            ?>
        </main>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script> 
    <script src="/js/dashboard.js"></script>
    <script src="/catmat-search/search.js"></script>
    <script src="/js/pesquisa-precos.js"></script>
    <script src="/js/analise-precos.js"></script>
    <script src="/js/pesquisa-orgaos.js"></script>
    <script src="/js/formulario-dinamico.js"></script>
    <script src="/js/solicitacao-lote.js"></script>
    <script src="/js/analise-precos.js"></script>
    <script src="https://unpkg.com/imask"></script>
    <script src="/js/masks.js"></script> </body>
    <script src="/js/cotacao-rapida.js"></script> </body>

</body>
</html>