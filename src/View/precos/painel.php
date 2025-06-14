<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisa de Preços - <?= htmlspecialchars($item['descricao']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center">
            <a href="/processos/<?= $processo['id'] ?>/itens" class="btn btn-sm btn-outline-secondary mb-2">Voltar para a Lista de Itens</a>
        </div>
        <div class="card bg-light p-3 mb-4">
            <h5 class="mb-1">Pesquisa de Preços para o Item: <strong><?= htmlspecialchars($item['descricao']) ?></strong></h5>
            <p class="mb-0 text-muted">Processo: <?= htmlspecialchars($processo['nome_processo']) ?></p>
            <?php if ($item['catmat_catser']): ?>
                <p class="mb-0 text-muted">CATMAT/CATSER: <?= htmlspecialchars($item['catmat_catser']) ?></p>
            <?php endif; ?>
        </div>

        <div class="row gx-4">
            <div class="col-md-5">
                <div class="card border-primary mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0 fs-5">Busca Automática</h4>
                    </div>
                    <div class="card-body text-center">
                        <p>Buscar preços recentes no Painel de Preços do Governo Federal.</p>
                        <button id="btnBuscarPainel" class="btn btn-primary" data-catmat="<?= htmlspecialchars($item['catmat_catser']) ?>">
                            <i class="fas fa-search-dollar"></i> Buscar no Painel de Preços
                        </button>
                        <div id="loadingPainel" class="spinner-border text-primary mt-3" role="status" style="display: none;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>

                <h4>Adicionar Cotação Manualmente</h4>
                <div class="card">
                    <div class="card-body">
                        <form action="/processos/<?= $processo['id'] ?>/itens/<?= $item['id'] ?>/precos" method="POST">
                            <div class="mb-3">
                                <label for="fonte" class="form-label">Fonte da Pesquisa</label>
                                <select class="form-select" id="fonte" name="fonte" required>
                                    <option value="Painel de Preços">Painel de Preços</option>
                                    <option value="Contratação Similar">Contratação Similar (Adm. Pública)</option>
                                    <option value="Site Especializado">Mídia / Site Especializado</option>
                                    <option value="Pesquisa com Fornecedor">Pesquisa Direta com Fornecedor</option>
                                    <option value="Nota Fiscal">Base de Notas Fiscais</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="valor" class="form-label">Valor (R$)</label>
                                <input type="number" step="0.01" class="form-control" id="valor" name="valor" required>
                            </div>
                            <div class="mb-3">
                                <label for="data_coleta" class="form-label">Data da Coleta</label>
                                <input type="date" class="form-control" id="data_coleta" name="data_coleta" required>
                            </div>
                            <div class="mb-3">
                                <label for="fornecedor_nome" class="form-label">Nome do Fornecedor (se aplicável)</label>
                                <input type="text" class="form-control" id="fornecedor_nome" name="fornecedor_nome">
                            </div>
                             <div class="mb-3">
                                <label for="fornecedor_cnpj" class="form-label">CNPJ do Fornecedor (se aplicável)</label>
                                <input type="text" class="form-control" id="fornecedor_cnpj" name="fornecedor_cnpj">
                            </div>
                            <div class="mb-3">
                                <label for="link_evidencia" class="form-label">Link da Evidência (se aplicável)</label>
                                <input type="url" class="form-control" id="link_evidencia" name="link_evidencia" placeholder="https://...">
                            </div>
                            <button type="submit" class="btn btn-success">Salvar Cotação</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <h4>Preços Coletados (<?= count($precos) ?>)</h4>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Fonte</th>
                                <th>Valor</th>
                                <th>Data</th>
                                <th>Fornecedor</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($precos)): ?>
                                <tr>
                                    <td colspan="5" class="text-center p-3 text-muted">Nenhuma cotação registrada ainda.</td>
                                </tr>
                            <?php endif; ?>
                            
                            <?php foreach ($precos as $preco): ?>
                                <tr>
                                    <td><?= htmlspecialchars($preco['fonte']) ?></td>
                                    <td>R$ <?= number_format($preco['valor'], 2, ',', '.') ?></td>
                                    <td><?= date('d/m/Y', strtotime($preco['data_coleta'])) ?></td>
                                    <td><?= htmlspecialchars($preco['fornecedor_nome']) ?: 'N/A' ?></td>
                                    <td></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalResultadosPainel" tabindex="-1" aria-labelledby="modalResultadosPainelLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalResultadosPainelLabel">Resultados do Painel de Preços</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Selecione uma das cotações abaixo para preencher o formulário de adição automaticamente.</p>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-secondary">
                        <tr>
                            <th>Preço Unit.</th>
                            <th>Fornecedor</th>
                            <th>Órgão Licitante (UASG)</th>
                            <th>Data</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaResultadosPainel">
                        </tbody>
                </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/pesquisa-precos.js"></script>
</body>
</html>