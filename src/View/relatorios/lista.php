<h1>Histórico de Relatórios Gerados</h1>
<div class="table-responsive mt-4">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Número da Nota</th>
                <th>Processo de Referência</th>
                <th>Gerada Por</th>
                <th>Data de Geração</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($notas as $nota): ?>
                <tr>
                    <td><strong><?= sprintf('%04d', $nota['numero_nota']) ?>/<?= $nota['ano_nota'] ?></strong></td>
                    <td><?= htmlspecialchars($nota['numero_processo']) ?> - <?= htmlspecialchars($nota['nome_processo']) ?></td>
                    <td><?= htmlspecialchars($nota['gerada_por']) ?></td>
                    <td><?= date('d/m/Y H:i:s', strtotime($nota['gerada_em'])) ?></td>
                    <td>
                        <a href="/processos/<?= $nota['processo_id'] ?>/relatorio?nota_id=<?= $nota['id'] ?>" target="_blank" class="btn btn-sm btn-secondary" title="Visualizar/Gerar Novamente">
                            <i class="bi bi-file-earmark-pdf"></i> Visualizar
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($notas)): ?>
                <tr><td colspan="5" class="text-center">Nenhum relatório foi gerado ainda.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>