document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formCotacaoRapida');
    if (!form) return;

    const areaResultados = document.getElementById('areaResultados');
    const loading = document.getElementById('loading');
    const boxResultados = document.getElementById('boxResultados');
    const estatisticasDiv = document.getElementById('resultadoEstatisticas');
    const tabelaDiv = document.getElementById('resultadoTabela');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Prepara a UI para a busca
        areaResultados.style.display = 'block';
        boxResultados.style.display = 'none';
        loading.style.display = 'block';
        estatisticasDiv.innerHTML = '';
        tabelaDiv.innerHTML = '';

        const formData = new FormData(form);
        const dados = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('/api/cotacao-rapida/buscar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            });

            if (!response.ok) {
                const erro = await response.json();
                throw new Error(erro.erro || 'Falha na comunicação com o servidor.');
            }

            const data = await response.json();
            
            loading.style.display = 'none';
            boxResultados.style.display = 'block';

            if (data.mensagem) {
                estatisticasDiv.innerHTML = `<div class="col-12"><div class="alert alert-warning">${data.mensagem}</div></div>`;
                return;
            }

            // Renderiza as estatísticas
            renderizarEstatisticas(data.estatisticas);

            // Renderiza a tabela de resultados
            renderizarTabela(data.resultados);

        } catch (error) {
            loading.style.display = 'none';
            boxResultados.style.display = 'block';
            estatisticasDiv.innerHTML = `<div class="col-12"><div class="alert alert-danger">${error.message}</div></div>`;
        }
    });

    function formatarMoeda(valor) {
        return parseFloat(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function renderizarEstatisticas(estatisticas) {
        estatisticasDiv.innerHTML = `
            <div class="col"><div class="card text-center h-100"><div class="card-header">Preços Encontrados</div><div class="card-body"><h5 class="card-title">${estatisticas.total}</h5></div></div></div>
            <div class="col"><div class="card text-center h-100"><div class="card-header">Menor Valor</div><div class="card-body"><h5 class="card-title">${formatarMoeda(estatisticas.minimo)}</h5></div></div></div>
            <div class="col"><div class="card text-center h-100"><div class="card-header">Valor Médio</div><div class="card-body"><h5 class="card-title">${formatarMoeda(estatisticas.media)}</h5></div></div></div>
            <div class="col"><div class="card text-center h-100"><div class="card-header">Valor Mediano</div><div class="card-body"><h5 class="card-title">${formatarMoeda(estatisticas.mediana)}</h5></div></div></div>
            <div class="col"><div class="card text-center h-100"><div class="card-header">Maior Valor</div><div class="card-body"><h5 class="card-title">${formatarMoeda(estatisticas.maximo)}</h5></div></div></div>
        `;
    }

    function renderizarTabela(resultados) {
        let tabelaHtml = `
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Fonte</th>
                        <th>Órgão / Fornecedor</th>
                        <th>Data</th>
                        <th>Preço Unitário</th>
                        <th>Unidade</th>
                    </tr>
                </thead>
                <tbody>
        `;

        resultados.forEach(item => {
            const data = new Date(item.dataResultado).toLocaleDateString('pt-BR');
            const fonte = item.nomeUasg ? 'Contratação Similar' : 'Painel de Preços';
            const nome = item.nomeUasg || item.nomeFornecedor;
            const unidade = item.siglaUnidadeFornecimento || 'N/A';

            tabelaHtml += `
                <tr>
                    <td>${fonte}</td>
                    <td>${nome}</td>
                    <td>${data}</td>
                    <td>${formatarMoeda(item.precoUnitario)}</td>
                    <td>${unidade}</td>
                </tr>
            `;
        });

        tabelaHtml += '</tbody></table>';
        tabelaDiv.innerHTML = tabelaHtml;
    }
});