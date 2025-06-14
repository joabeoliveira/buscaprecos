<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Processos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Dashboard de Processos</h1>
            <a href="/processos/novo" class="btn btn-primary">
                <i class="fas fa-plus"></i> Adicionar Novo Processo
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nº do Processo</th>
                        <th>Nome</th>
                        <th>Agente Responsável</th>
                        <th>UASG</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($processos as $processo): ?>
                        <tr>
                            <td><?= htmlspecialchars($processo['numero_processo']) ?></td>
                            <td><?= htmlspecialchars($processo['nome_processo']) ?></td>
                            <td><?= htmlspecialchars($processo['agente_responsavel'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($processo['uasg'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($processo['tipo_contratacao']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($processo['status']) ?></span></td>
                            <td>
                                <a href="/processos/<?= $processo['id'] ?>/itens" class="btn btn-sm btn-success" title="Ver Itens">
                                    <i class="fas fa-list-ul"></i> Itens
                                </a>
                                <a href="/processos/<?= $processo['id'] ?>/editar" class="btn btn-sm btn-primary" title="Editar Processo">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="/processos/<?= $processo['id'] ?>/excluir" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este processo? Todos os itens e preços coletados serão perdidos.');">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Excluir Processo">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($processos)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Nenhum processo encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>