<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Processo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Editar Processo</h1>

        <form action="/processos/<?= $processo['id'] ?>/editar" method="POST" class="mt-4">
            <div class="mb-3">
                <label for="numero_processo" class="form-label">Número do Processo</label>
                <input type="text" class="form-control" id="numero_processo" name="numero_processo" value="<?= htmlspecialchars($processo['numero_processo']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="nome_processo" class="form-label">Nome do Processo</label>
                <input type="text" class="form-control" id="nome_processo" name="nome_processo" value="<?= htmlspecialchars($processo['nome_processo']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="tipo_contratacao" class="form-label">Tipo de Contratação</label>
                <select class="form-select" id="tipo_contratacao" name="tipo_contratacao" required>
                    <option <?= $processo['tipo_contratacao'] == 'Pregão Eletrônico' ? 'selected' : '' ?>>Pregão Eletrônico</option>
                    <option <?= $processo['tipo_contratacao'] == 'Dispensa de Licitação' ? 'selected' : '' ?>>Dispensa de Licitação</option>
                    <option <?= $processo['tipo_contratacao'] == 'Inexigibilidade' ? 'selected' : '' ?>>Inexigibilidade</option>
                    <option <?= $processo['tipo_contratacao'] == 'Compra Direta' ? 'selected' : '' ?>>Compra Direta (Pequeno Valor)</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option <?= $processo['status'] == 'Em Elaboração' ? 'selected' : '' ?>>Em Elaboração</option>
                    <option <?= $processo['status'] == 'Pesquisa em Andamento' ? 'selected' : '' ?>>Pesquisa em Andamento</option>
                    <option <?= $processo['status'] == 'Finalizado' ? 'selected' : '' ?>>Finalizado</option>
                    <option <?= $processo['status'] == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>

            <a href="/dashboard" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </form>
    </div>
</body>
</html>