<div class="container-xl mt-4 mb-5">
    <a href="/processos/<?= $processo['id'] ?>/itens" class="btn btn-sm btn-outline-secondary mb-2">Voltar para a Lista de Itens</a>
    <h1>Editar Item do Processo: <small class="text-muted"><?= htmlspecialchars($processo['nome_processo']) ?></small></h1>

    <form action="/processos/<?= $processo['id'] ?>/itens/<?= $item['id'] ?>/editar" method="POST" class="mt-4">
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="numero_item" class="form-label">Nº do Item</label>
                <input type="number" class="form-control" id="numero_item" name="numero_item" value="<?= htmlspecialchars($item['numero_item']) ?>" required>
            </div>
            <div class="col-md-9 mb-3">
                <label for="catmat_input" class="form-label">Código CATMAT/CATSER</label>
                <input type="text" class="form-control" id="catmat_input" name="catmat_catser" value="<?= htmlspecialchars($item['catmat_catser']) ?>">
            </div>
        </div>
        <div class="mb-3">
            <label for="descricao_input" class="form-label">Descrição do Item</label>
            <textarea class="form-control" id="descricao_input" name="descricao" rows="3" required><?= htmlspecialchars($item['descricao']) ?></textarea>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" class="form-control" id="quantidade" name="quantidade" value="<?= htmlspecialchars($item['quantidade']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="unidade_medida" class="form-label">Unidade de Medida</label>
                <input type="text" class="form-control" id="unidade_medida" name="unidade_medida" value="<?= htmlspecialchars($item['unidade_medida']) ?>" required>
            </div>
        </div>

        <hr>
        <div class="d-flex justify-content-end gap-2">
            <a href="/processos/<?= $processo['id'] ?>/itens" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </div>
    </form>
</div>