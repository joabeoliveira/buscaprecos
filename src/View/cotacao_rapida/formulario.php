<style>
    .preco-row.desconsiderado {
        opacity: 0.5;
        text-decoration: line-through;
        background-color: #f8f9fa;
    }
    .preco-row.desconsiderado .badge {
        opacity: 0.65;
    }
</style>

<h1 class="mb-4">Cotação Rápida (Incisos I e II da IN 65/2021)</h1>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <label for="nomeResponsavel" class="form-label"><strong>Nome do Responsável pela Pesquisa</strong></label>
        <input type="text" class="form-control" id="nomeResponsavel" placeholder="Digite seu nome completo..." required>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header"><h5 class="mb-0">Itens para Pesquisa</h5></div>
    <div class="card-body">
        <form id="formCotacaoRapida">
            <div id="itens-container">
                <div class="row item-row mb-3 align-items-center">
                    <div class="col-md-3">
                        <label class="form-label"><strong>Código CATMAT/CATSER</strong></label>
                        <input type="number" class="form-control" name="catmat[]" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Descrição do Item (Opcional)</label>
                        <input type="text" class="form-control" name="descricao[]">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label"><strong>Quantidade</strong></label>
                        <input type="number" class="form-control" name="quantidade[]" value="1" min="1" required>
                    </div>
                    <div class="col-md-2">
                    <label class="form-label">Região (UF)</label>
                        <select class="form-select" name="regiao[]">
                            <option value="" selected>Todas</option>
                            <?php $estados = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO']; ?>
                            <?php foreach ($estados as $uf): ?><option value="<?= $uf ?>"><?= $uf ?></option><?php endforeach; ?>
                        </select>
                        </div>
                        <div class="col-md-1 pt-4 text-center">
                            <button type="button" class="btn btn-danger btn-sm remove-item" title="Remover este item"><i class="bi bi-trash"></i></button>  
                        </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
    <div>
        <button type="button" class="btn btn-success" id="btn-adicionar-item"><i class="bi bi-plus-lg"></i> Adicionar outro item</button>
        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalImportarItens">
            <i class="bi bi-file-earmark-spreadsheet"></i> Importar via Planilha
        </button>
        </div>
    <button type="submit" class="btn btn-primary btn-lg px-4"><i class="bi bi-search"></i> Buscar Preços</button>
</div>

            <div class="modal fade" id="modalImportarItens" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Importar Itens da Planilha</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted">A planilha deve conter o CATMAT (coluna A), a Descrição (coluna B) e a Quantidade (coluna C).</p>
                            <div class="mb-3">
                                <a href="/cotacao-rapida/modelo-planilha" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> Baixar modelo da planilha
                                </a>
                            </div>
                            <label for="inputPlanilha" class="form-label"><strong>Selecione o arquivo:</strong></label>
                            <input type="file" id="inputPlanilha" class="form-control" accept=".xlsx, .xls, .csv">
                            <div id="loadingImport" class="spinner-border spinner-border-sm mt-2" style="display: none;"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="btnConfirmarImportacao">Importar e Preencher</button>
                        </div>
                    </div>
                </div>
            </div>


        </form>
    </div>
</div>

<div id="areaResultados" class="mt-5" style="display: none;">
    
    <div id="loading" class="text-center" style="display: none;">
        <div class="spinner-border text-primary" role="status" style="height: 3rem; width: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 fs-5">Buscando... Isso pode levar alguns segundos.</p>
    </div>

    <div id="boxResultados"></div>

    <div id="areaSalvarRelatorio" class="mt-4 card shadow-sm" style="display: none;">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Salvar Análise e Gerar Relatório</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-8">
                    <label for="tituloCotacao" class="form-label"><strong>Título para esta Cotação Rápida</strong></label>
                    <input type="text" id="tituloCotacao" class="form-control" placeholder="Ex: Cotação de notebooks para o Depto. de TI" required>
                    <div class="form-text">Este título ajudará a identificar a cotação no histórico de relatórios.</div>
                </div>
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-success btn-lg" id="btnSalvarRelatorio">
                        <i class="bi bi-check-circle-fill"></i> Salvar e Gerar PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<template id="item-row-template">
    <div class="row item-row mb-3 align-items-center">
        <div class="col-md-3">
            <label class="form-label"><strong>Código CATMAT/CATSER</strong></label>
            <input type="number" class="form-control" name="catmat[]" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Descrição do Item (Opcional)</label>
            <input type="text" class="form-control" name="descricao[]">
        </div>
        <div class="col-md-2">
            <label class="form-label"><strong>Quantidade</strong></label>
            <input type="number" class="form-control" name="quantidade[]" value="1" min="1" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Região (UF)</label>
            <select class="form-select" name="regiao[]">
                <option value="" selected>Todas</option>
                <?php $estados = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO']; ?>
                <?php foreach ($estados as $uf): ?><option value="<?= $uf ?>"><?= $uf ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-1 pt-4 text-center">
            </div>
    </div>
</template>