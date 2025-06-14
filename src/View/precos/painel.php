<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pesquisa de Preços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
    <div class="container mt-4">
        <a href="/processos/<?= $processo['id'] ?>/itens" class="btn btn-sm btn-outline-secondary mb-2">Voltar para a Lista de Itens</a>
        <div class="card bg-light p-3 mb-4">
            <h5 class="mb-1">Pesquisa de Preços para o Item: <strong><?= htmlspecialchars($item['descricao']) ?></strong></h5>
            <p class="mb-0 text-muted">Processo: <?= htmlspecialchars($processo['nome_processo']) ?></p>
        </div>

        <div class="row">
            <div class="col-md-5">
    <h4>Adicionar Nova Cotação</h4>
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
                <table class="table table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>Fonte</th>
                            <th>Valor</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($precos)): ?>
                            <tr>
                                <td colspan="4" class="text-center p-3">Nenhuma cotação registrada ainda.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($precos as $preco): ?>
                            <tr>
                                <td><?= htmlspecialchars($preco['fonte']) ?></td>
                                <td>R$ <?= number_format($preco['valor'], 2, ',', '.') ?></td>
                                <td><?= date('d/m/Y', strtotime($preco['data_coleta'])) ?></td>
                                <td></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>