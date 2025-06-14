// Garante que o código só rode após o HTML estar 100% pronto.
document.addEventListener('DOMContentLoaded', () => {

    const btnBuscarPainel = document.getElementById('btnBuscarPainel');
    const loadingPainel = document.getElementById('loadingPainel');
    const tabelaResultados = document.getElementById('tabelaResultadosPainel');
    
    let modalResultados = null;
    const modalElement = document.getElementById('modalResultadosPainel');
    if (modalElement) {
        modalResultados = new bootstrap.Modal(modalElement);
    }

    if (!btnBuscarPainel) {
        return; // Sai se o botão não estiver na página
    }

    btnBuscarPainel.addEventListener('click', async () => {
        const catmat = btnBuscarPainel.dataset.catmat;
        if (!catmat) {
            alert('O item não possui um código CATMAT para a busca.');
            return;
        }

        loadingPainel.style.display = 'block';
        btnBuscarPainel.disabled = true;

        try {
            const response = await fetch('/api/painel-de-precos', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ catmat: catmat })
            });

            const data = await response.json();
            
            tabelaResultados.innerHTML = ''; // Limpa resultados antigos

            if (data.resultado && data.resultado.length > 0) {
    data.resultado.forEach(item => {
        const tr = document.createElement('tr');

        // --- INÍCIO DA CORREÇÃO DEFINITIVA ---
        // Construindo o HTML com concatenação de strings (+) em vez de template literals (`)
        // Isso é mais antigo, mas garantido de funcionar.
        let htmlDaLinha = '';
        htmlDaLinha += '<td>R$ ' + parseFloat(item.precoUnitario).toFixed(2).replace('.', ',') + '</td>';
        htmlDaLinha += '<td>' + (item.nomeFornecedor || 'N/A') + '</td>';
        htmlDaLinha += '<td>' + (item.nomeUasg || 'N/A') + '</td>';
        htmlDaLinha += '<td>' + new Date(item.dataResultado).toLocaleDateString('pt-BR') + '</td>';
        
        // Criamos o botão separadamente para lidar com as aspas do JSON
        const itemDataString = JSON.stringify(item).replace(/'/g, "&apos;"); // Escapa aspas simples
        htmlDaLinha += "<td><button class='btn btn-sm btn-outline-success btn-adicionar-cota' data-item='" + itemDataString + "'>Adicionar</button></td>";

        tr.innerHTML = htmlDaLinha;
        // --- FIM DA CORREÇÃO DEFINITIVA ---

        tabelaResultados.appendChild(tr);
    });
} else {
    tabelaResultados.innerHTML = '<tr><td colspan="5" class="text-center">Nenhum resultado encontrado.</td></tr>';
}


            if (modalResultados) {
                modalResultados.show();
            }

        } catch (error) {
            console.error('Erro:', error);
            alert('Ocorreu um erro ao buscar os dados.');
        } finally {
            loadingPainel.style.display = 'none';
            btnBuscarPainel.disabled = false;
        }
    });

    // Evento para os botões "Adicionar" que são criados dinamicamente
    document.body.addEventListener('click', (event) => {
        if (event.target.classList.contains('btn-adicionar-cota')) {
            const itemData = JSON.parse(event.target.dataset.item);

            document.getElementById('fonte').value = 'Painel de Preços';
            document.getElementById('valor').value = parseFloat(itemData.precoUnitario).toFixed(2);
            document.getElementById('data_coleta').value = itemData.dataResultado.split('T')[0];
            document.getElementById('fornecedor_nome').value = itemData.nomeFornecedor;
            document.getElementById('fornecedor_cnpj').value = itemData.niFornecedor;

            if (modalResultados) {
                modalResultados.hide();
            }
            //alert('Formulário preenchido com a cotação selecionada!');
        }
    });
});