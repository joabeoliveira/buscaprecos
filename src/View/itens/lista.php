<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Itens do Processo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="/catmat-search/style.css">
</head>
<body>
    <div class="container mt-4">
        <a href="/dashboard" class="btn btn-sm btn-outline-secondary mb-2">Voltar para Processos</a>
        <h1>Itens do Processo: <small class="text-muted"><?= htmlspecialchars($processo['nome_processo']) ?></small></h1>

        <?php if (isset($_GET['erro']) && $_GET['erro'] === 'duplicado'): ?>
            <div class="alert alert-danger mt-3">
                Erro: Já existe um item com este Número ou CATMAT neste processo.
            </div>
        <?php endif; ?>

        <h4 class="mt-4">Itens Cadastrados</h4>
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Nº</th>
                    <th>Descrição</th>
                    <th>CATMAT/CATSER</th>
                    <th>Qtd.</th>
                    <th>Unidade</th>
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
                <a href="/processos/<?= $processo['id'] ?>/itens/<?= $item['id'] ?>/editar" class="btn btn-sm btn-primary" title="Editar Item">
                    <i class="fas fa-edit"></i>
                </a>

                <a href="/processos/<?= $processo['id'] ?>/itens/<?= $item['id'] ?>/pesquisar" class="btn btn-sm btn-success" title="Pesquisar Preços">
                    <i class="fas fa-dollar-sign"></i>
                </a>

                <form action="/processos/<?= $processo['id'] ?>/itens/<?= $item['id'] ?>/excluir" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este item?');">
                    <button type="submit" class="btn btn-sm btn-danger" title="Excluir Item">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
        </td> 
    </tr> <?php endforeach; ?>

                <?php if (empty($itens)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Nenhum item cadastrado para este processo.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <hr>

        <div class="d-flex justify-content-end my-3">
            <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#formularioAdicionarItem" aria-expanded="false" aria-controls="formularioAdicionarItem">
                <i class="fas fa-plus"></i> Adicionar Novo Item
            </button>
        </div>

        <div class="collapse mb-4" id="formularioAdicionarItem">
            <div class="card card-body">
                <h4>Novo Item</h4>
                <form action="/processos/<?= $processo['id'] ?>/itens" method="POST" class="mt-2">
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label for="numero_item" class="form-label">Nº do Item</label>
                            <input type="number" class="form-control" id="numero_item" name="numero_item" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="catmat_input" class="form-label">Código CATMAT/CATSER</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="catmat_input" name="catmat_catser">
                                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalBuscaCatmat">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="descricao_input" class="form-label">Descrição do Item</label>
                        <textarea class="form-control" id="descricao_input" name="descricao" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="quantidade" class="form-label">Quantidade</label>
                            <input type="number" class="form-control" id="quantidade" name="quantidade" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="unidade_medida" class="form-label">Unidade de Medida</label>
                            <input type="text" class="form-control" id="unidade_medida" name="unidade_medida" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Salvar Item</button>
                </form>
            </div>
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
                    <button class="btn btn-outline-secondary" type="button" id="btnLimpar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="results-container">
                    <ul id="listaSugestoes" class="list-group"></ul>
                </div>
            </div>
        </div>
    </div>
</div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <script src="/catmat-search/search.js"></script>
</body>
</html>