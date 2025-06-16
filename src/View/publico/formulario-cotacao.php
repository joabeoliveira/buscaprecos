<h3 class="mb-3">Resposta de Cotação</h3>
<p><strong>Fornecedor:</strong> <?= htmlspecialchars($solicitacao['razao_social']) ?></p>
<p><strong>Processo de Referência:</strong> <?= htmlspecialchars($solicitacao['nome_processo']) ?></p>
<p class="text-danger"><strong>Prazo para resposta:</strong> <?= date('d/m/Y', strtotime($solicitacao['prazo_final'])) ?></p>
<hr>

<form action="/cotacao/responder" method="POST">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    
    <p>Por favor, preencha o valor unitário para os itens abaixo. Deixe em branco os itens que não deseja cotar.</p>

    <?php foreach ($itens as $item): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title"><?= htmlspecialchars($item['descricao']) ?></h6>
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="preco_<?= $item['id'] ?>" class="form-label">Preço Unitário (R$)</label>
                        <input type="number" step="0.01" class="form-control" name="precos[<?= $item['id'] ?>][valor]" id="preco_<?= $item['id'] ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Unidade de Medida</label>
                        <input type="text" class="form-control" name="precos[<?= $item['id'] ?>][unidade_medida]" value="<?= htmlspecialchars($item['unidade_medida']) ?>" readonly>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">Enviar Cotação</button>
    </div>
</form>