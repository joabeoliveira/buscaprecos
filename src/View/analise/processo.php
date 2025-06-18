<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="/processos/<?= $processo['id'] ?>/itens" class="btn btn-sm btn-outline-secondary mb-2">Voltar para a Lista de Itens</a>
            <h1>Mesa de Análise Geral</h1>
            <p class="text-muted">Processo: <strong><?= htmlspecialchars($processo['nome_processo']) ?></strong></p>
        </div>
    </div>
    
    <?php foreach($itensComAnalise as $analise): 
        $item = $analise['item'];
        $precos = $analise['precos'];
        $estatisticas = $analise['estatisticas'];
    ?>
    <div class="card mb-5 shadow-sm">
        <div class="card-header bg-light">
            <h4 class="mb-0">Item <?= htmlspecialchars($item['numero_item']) ?>: <?= htmlspecialchars($item['descricao']) ?></h4>
        </div>
        <div class="card-body p-4">
            <h6>Estatísticas da Cesta de Preços Válida</h6>
            <div class="row mb-4 g-3">
                <div class="col">
                    <div class="card text-center h-100"><div class="card-header">Cotações Válidas</div><div class="card-body"><h5 class="card-title"><?= $estatisticas['total'] ?></h5></div></div>
                </div>
                <div class="col">
                    <div class="card text-center h-100"><div class="card-header">Mínimo</div><div class="card-body"><h5 class="card-title">R$ <?= number_format($estatisticas['minimo'], 2, ',', '.') ?></h5></div></div>
                </div>
                <div class="col">
                    <div class="card text-center h-100"><div class="card-header">Médio</div><div class="card-body"><h5 class="card-title">R$ <?= number_format($estatisticas['media'], 2, ',', '.') ?></h5></div></div>
                </div>
                <div class="col">
                    <div class="card text-center h-100"><div class="card-header">Mediano</div><div class="card-body"><h5 class="card-title">R$ <?= number_format($estatisticas['mediana'], 2, ',', '.') ?></h5></div></div>
                </div>
                <div class="col">
                    <div class="card text-center h-100"><div class="card-header">Máximo</div><div class="card-body"><h5 class="card-title">R$ <?= number_format($estatisticas['maximo'], 2, ',', '.') ?></h5></div></div>
                </div>
            </div>
            
            <h6 class="mt-4">Curadoria da Cesta de Preços</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">Status</th>
                            <th>Fonte</th>
                            <th>Valor Unitário</th>
                            <th>Unidade</th>
                            <th>Fornecedor/Órgão</th>
                            <th>Data</th>
                            <th style="width: 15%;">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                         <?php if (empty($precos)): ?>
                            <tr><td colspan="7" class="text-center">Nenhum preço coletado para este item.</td></tr>
                        <?php endif; ?>
                        <?php foreach($precos as $preco): ?>
                             <tr class="<?= $preco['status_analise'] == 'desconsiderado' ? 'table-danger' : '' ?>">
                                <td>
                                    <?php if($preco['status_analise'] == 'desconsiderado'): ?>
                                        <span class="badge bg-danger">Desconsiderado</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Considerado</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($preco['fonte']) ?></td>
                                <td><strong>R$ <?= number_format($preco['valor'], 2, ',', '.') ?></strong></td>
                                <td><?= htmlspecialchars($preco['unidade_medida'] ?? '') ?></td>
                                <td><?= htmlspecialchars($preco['fornecedor_nome'] ?: 'N/A') ?></td>
                                <td><?= date('d/m/Y', strtotime($preco['data_coleta'])) ?></td>
                                <td>
                                    <?php if($preco['status_analise'] == 'desconsiderado'): ?>
                                        <form action="/processos/<?= $processo['id'] ?>/itens/<?= $item['id'] ?>/precos/<?= $preco['id'] ?>/reconsiderar" method="POST" class="d-inline">
                                            <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check-circle"></i> Reconsiderar</button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-desconsiderar" 
                                                data-bs-toggle="modal" data-bs-target="#modalDesconsiderar"
                                                data-processo-id="<?= $processo['id'] ?>"
                                                data-item-id="<?= $item['id'] ?>"
                                                data-preco-id="<?= $preco['id'] ?>">
                                            <i class="bi bi-trash"></i> Desconsiderar
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if($preco['status_analise'] == 'desconsiderado' && !empty($preco['justificativa_descarte'])): ?>
                                <tr class="table-danger"><td colspan="7" class="small p-2 text-muted"><strong>Justificativa:</strong> <?= htmlspecialchars($preco['justificativa_descarte']) ?></td></tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <hr class="my-4">
            <div class="p-3 bg-light border rounded">
                <h5 class="mb-3">Definição do Preço Estimado para o Item</h5>
                <form action="/processos/<?= $processo['id'] ?>/itens/<?= $item['id'] ?>/salvar-analise" method="POST">
                    <div class="row">
                        <div class="col-md-7">
                            <label class="form-label"><strong>Escolha a Metodologia:</strong></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="metodologia_estimativa" id="metodoMedia_<?= $item['id'] ?>" value="Média" <?= ($item['metodologia_estimativa'] ?? '') == 'Média' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="metodoMedia_<?= $item['id'] ?>">
                                    Usar a Média (R$ <?= number_format($estatisticas['media'], 2, ',', '.') ?>)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="metodologia_estimativa" id="metodoMediana_<?= $item['id'] ?>" value="Mediana" <?= ($item['metodologia_estimativa'] ?? '') == 'Mediana' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="metodoMediana_<?= $item['id'] ?>">
                                    Usar a Mediana (R$ <?= number_format($estatisticas['mediana'], 2, ',', '.') ?>)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="metodologia_estimativa" id="metodoMenor_<?= $item['id'] ?>" value="Menor Valor" <?= ($item['metodologia_estimativa'] ?? '') == 'Menor Valor' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="metodoMenor_<?= $item['id'] ?>">
                                    Usar o Menor Valor (R$ <?= number_format($estatisticas['minimo'], 2, ',', '.') ?>)
                                </label>
                            </div>
                             <div class="form-check">
                                <input class="form-check-input" type="radio" name="metodologia_estimativa" id="metodoManual_<?= $item['id'] ?>" value="Manual" <?= ($item['metodologia_estimativa'] ?? '') == 'Manual' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="metodoManual_<?= $item['id'] ?>">
                                    Definir Valor Manualmente
                                </label>
                            </div>
                            <input type="number" step="0.01" name="valor_manual" class="form-control form-control-sm mt-2" placeholder="Digite o valor manual aqui..." value="<?= htmlspecialchars($item['valor_estimado'] ?? '') ?>">
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="justificativa_estimativa_<?= $item['id'] ?>" class="form-label"><strong>Justificativa da Metodologia:</strong></label>
                                <textarea name="justificativa_estimativa" id="justificativa_estimativa_<?= $item['id'] ?>" class="form-control" rows="5" required><?= htmlspecialchars($item['justificativa_estimativa'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary">Salvar Análise do Item</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
    <?php endforeach; ?>

    <div class="modal fade" id="modalDesconsiderar" tabindex="-1" aria-labelledby="modalDesconsiderarLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="formDesconsiderar" method="POST">
            <div class="modal-header">
              <h5 class="modal-title" id="modalDesconsiderarLabel">Desconsiderar Preço</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label for="justificativa_descarte" class="form-label">Justificativa (Obrigatório)</label>
                <textarea class="form-control" id="justificativa_descarte" name="justificativa_descarte" rows="4" required></textarea>
                <div class="form-text">Explique por que este preço é inexequível, inconsistente ou excessivamente elevado.</div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-danger">Confirmar Descarte</button>
            </div>
          </form>
        </div>
      </div>
    </div>
</div>