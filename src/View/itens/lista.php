<?php
// LÓGICA DAS FLASH MESSAGES
$mensagemFlash = null;
$dadosFormulario = [];
$abrirFormulario = false;

if (isset($_SESSION['flash'])) {
    $mensagemFlash = $_SESSION['flash'];
    if (!empty($mensagemFlash['dados_formulario'])) {
        $dadosFormulario = $mensagemFlash['dados_formulario'];
        $abrirFormulario = true;
    }
    unset($_SESSION['flash']);
}
?>

<div class="d-flex justify-content-between align-items-center">
    <div>
        <a href="/processos" class="btn btn-sm btn-outline-secondary mb-2">Voltar para Processos</a>
        <h1>Itens do Processo: <small class="text-muted"><?= htmlspecialchars($processo['nome_processo']) ?></small></h1>
    </div>
</div>

<?php if ($mensagemFlash): ?>
    <div class="alert alert-<?= htmlspecialchars($mensagemFlash['tipo']) ?> mt-3 alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($mensagemFlash['mensagem']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<h4 class="mt-4">Itens Cadastrados</h4>
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Nº</th>
                <th style="width: 50%;">Descrição</th>
                <th>CATMAT/CATSER</th>
                <th>Qtd.</th>
                <th>Unid. Medida</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itens as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['numero_item']) ?></td>
                    <td><?= htmlspecialchars($item['descricao']) ?></td>
                    <td><?= htmlspecialchars($item['catmat_catser']) ?></td>
                    <td><?= htmlspecialchars($item['quantidade']) ?></td>
                    <td><?= htmlspecialchars($item['unidade_medida']) ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="/processos/<?= $processo['id'] ?>/itens/<?= $item['id'] ?>/editar" class="btn btn-sm btn-primary" title="Editar Item"><i class="bi bi-pencil-square"></i></a>
                            <a href="/processos/<?= $processo['id'] ?>/itens/<?= $item['id'] ?>/pesquisar" class="btn btn-sm btn-success" title="Pesquisar Preços"><i class="bi bi-search-heart"></i></a>
                            <form action="/processos/<?= $processo['id'] ?>/itens/<?= $item['id'] ?>/excluir" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este item?');">
                                <button type="submit" class="btn btn-sm btn-danger" title="Excluir Item"><i class="bi bi-trash-fill"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($itens)): ?>
                <tr>
                    <td colspan="6" class="text-center">Nenhum item cadastrado para este processo.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<hr>

<div class="d-flex justify-content-end my-3">
    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#formularioAdicionarItem" aria-expanded="false" aria-controls="formularioAdicionarItem">
        <i class="bi bi-plus-lg"></i> Adicionar Novo Item
    </button>
</div>

<div class="collapse mb-4 <?= $abrirFormulario ? 'show' : '' ?>" id="formularioAdicionarItem">
    <div class="card shadow-sm border-light-subtle">
        
        <div class="card-header bg-light">
            <h4 class="mb-0">Adicionar Novo Item ao Processo</h4>
        </div>
        
        <div class="card-body">
            <form action="/processos/<?= $processo['id'] ?>/itens" method="POST" class="mt-2">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="numero_item" class="form-label">Nº do Item</label>
                        <input type="number" class="form-control" id="numero_item" name="numero_item" required value="<?= htmlspecialchars($dadosFormulario['numero_item'] ?? '') ?>">
                    </div>
                    <div class="col-md-9 mb-3">
                        <label for="catmat_input" class="form-label">Código CATMAT/CATSER</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="catmat_input" name="catmat_catser" value="<?= htmlspecialchars($dadosFormulario['catmat_catser'] ?? '') ?>">
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalBuscaCatmat">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="descricao_input" class="form-label">Descrição Detalhada do Item</label>
                    <textarea class="form-control" id="descricao_input" name="descricao" rows="3" required><?= htmlspecialchars($dadosFormulario['descricao'] ?? '') ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <input type="number" class="form-control" id="quantidade" name="quantidade" required value="<?= htmlspecialchars($dadosFormulario['quantidade'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="unidade_medida" class="form-label">Unidade de Medida</label>
                        <input type="text" class="form-control" id="unidade_medida" name="unidade_medida" required value="<?= htmlspecialchars($dadosFormulario['unidade_medida'] ?? '') ?>">
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#formularioAdicionarItem">Cancelar</button>
                    <button type="submit" class="btn btn-success">Salvar Item</button>
                </div>
            </form>
        </div>
    </div>

<div class="modal fade" id="modalBuscaCatmat" tabindex="-1" aria-labelledby="modalBuscaCatmatLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalBuscaCatmatLabel">Busca Inteligente de Materiais</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3 search-box">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="inputBuscaModal" class="form-control" placeholder="Digite o nome ou código do material..." autocomplete="off" />
                        <button class="btn btn-outline-secondary" type="button" id="btnLimpar"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="results-container">
                    <ul id="listaSugestoes" class="list-group"></ul>
                </div>
            </div>
        </div>
    </div>
</div>