<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Processos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Dashboard de Processos</h1>
            <a href="/processos/novo" class="btn btn-primary">Adicionar Novo Processo</a>
        </div>

        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Nº do Processo</th>
                    <th>Nome</th>
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
                        <td><?= htmlspecialchars($processo['tipo_contratacao']) ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($processo['status']) ?></span></td>
                            <td>
                                <a href="/processos/<?= $processo['id'] ?>/editar" class="btn btn-sm btn-primary">Editar</a>
                                <a href="#" class="btn btn-sm btn-info">Ver Itens</a>
                            </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($processos)): ?>
                    <tr>
                        <td colspan="5" class="text-center">Nenhum processo encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>