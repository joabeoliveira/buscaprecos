<h1 class="mb-4">Cotação Rápida (Incisos I e II)</h1>

<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="mb-0">Formulário de Pesquisa</h5>
    </div>
    <div class="card-body">
        <form id="formCotacaoRapida">
            <div class="row">
                <div class="col-md-3">
                    <label for="catmat" class="form-label"><strong>Código CATMAT</strong></label>
                    <input type="number" class="form-control" id="catmat" name="catmat" required>
                </div>
                <div class="col-md-6">
                    <label for="descricao" class="form-label">Descrição do Item (Opcional)</label>
                    <input type="text" class="form-control" id="descricao" name="descricao">
                </div>
                <div class="col-md-3">
                    <label for="regiao" class="form-label">Região (UF) (Opcional)</label>
                    <select class="form-select" id="regiao" name="regiao">
                        <option value="" selected>Todas</option>
                        <?php $estados = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO']; ?>
                        <?php foreach ($estados as $uf): ?>
                            <option value="<?= $uf ?>"><?= $uf ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary btn-lg px-4">
                    <i class="bi bi-search"></i> Buscar Preços
                </button>
            </div>
        </form>
    </div>
</div>

<div id="areaResultados" class="mt-5" style="display: none;">
    <div id="loading" class="text-center" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Buscando... Isso pode levar alguns segundos.</p>
    </div>

    <div id="boxResultados" style="display: none;">
        <h2>Resultados da Pesquisa</h2>
        <div id="resultadoEstatisticas" class="row mb-4 g-3">
            </div>
        <div id="resultadoTabela" class="table-responsive">
            </div>
    </div>
</div>