<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Fornecedores</h1>
    <a href="/fornecedores/novo" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Adicionar Novo Fornecedor
    </a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Razão Social</th>
                <th>CNPJ</th>
                <th>E-mail</th>
                <th>Ramo de Atividade</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fornecedores as $fornecedor): ?>
                <tr>
                    <td><?= htmlspecialchars($fornecedor['razao_social']) ?></td>
                    <td><?= htmlspecialchars($fornecedor['cnpj']) ?></td>
                    <td><?= htmlspecialchars($fornecedor['email']) ?></td>
                    <td><?= htmlspecialchars($fornecedor['ramo_atividade']) ?></td>
                    <td>
                        <a href="#" class="btn btn-sm btn-primary" title="Editar Fornecedor">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($fornecedores)): ?>
                <tr>
                    <td colspan="5" class="text-center">Nenhum fornecedor encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>