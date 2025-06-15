document.addEventListener('DOMContentLoaded', () => {
    const btnBuscarPainel = document.getElementById('btnBuscarPainel');
    const loadingPainel = document.getElementById('loadingPainel');
    const tabelaResultados = document.getElementById('tabelaResultadosPainel');
    const btnAdicionarSelecionados = document.getElementById('btnAdicionarSelecionados');
    
    let modalResultados = null;
    const modalElement = document.getElementById('modalResultadosPainel');
    if (modalElement) {
        modalResultados = new bootstrap.Modal(modalElement);
    }

    if (!btnBuscarPainel) return;

    // Função para buscar no Painel de Preços
    btnBuscarPainel.addEventListener('click', async () => {
        // ... (código de loading e validação do catmat)
        loadingPainel.style.display = 'block';
        btnBuscarPainel.disabled = true;

        try {
            const catmat = btnBuscarPainel.dataset.catmat;
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
                    const itemDataString = JSON.stringify(item).replace(/'/g, "&apos;");

                    tr.innerHTML = `
                        <td><input type="checkbox" class="form-check-input cota-checkbox" data-item='${itemDataString}'></td>
                        <td>R$ ${parseFloat(item.precoUnitario).toFixed(2).replace('.', ',')}</td>
                        <td>${item.siglaUnidadeFornecimento || 'N/A'}</td>
                        <td>${item.capacidadeUnidadeFornecimento > 0 ? `${item.capacidadeUnidadeFornecimento} ${item.siglaUnidadeMedida}` : 'N/A'}</td>
                        <td>${item.nomeFornecedor || 'N/A'}</td>
                        <td>${new Date(item.dataResultado).toLocaleDateString('pt-BR')}</td>
                    `;
                    tabelaResultados.appendChild(tr);
                });
            } else {
                tabelaResultados.innerHTML = '<tr><td colspan="6" class="text-center">Nenhum resultado encontrado.</td></tr>';
            }
            modalResultados.show();
        } catch (error) {
            console.error('Erro:', error);
            alert('Ocorreu um erro ao buscar os dados.');
        } finally {
            loadingPainel.style.display = 'none';
            btnBuscarPainel.disabled = false;
        }
    });

    // Função para adicionar os itens selecionados ao formulário principal
    btnAdicionarSelecionados.addEventListener('click', () => {
        const checkboxes = document.querySelectorAll('.cota-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Por favor, selecione pelo menos uma cotação.');
            return;
        }

        checkboxes.forEach(box => {
            const itemData = JSON.parse(box.dataset.item);

            // Monta a string completa da unidade para o formulário
            let unidadeCompleta = itemData.siglaUnidadeFornecimento || '';
            if (itemData.capacidadeUnidadeFornecimento > 0 && itemData.siglaUnidadeMedida) {
                unidadeCompleta += ` c/ ${itemData.capacidadeUnidadeFornecimento} ${itemData.siglaUnidadeMedida}`;
            }

            // Preenche e envia um formulário para cada item selecionado
            preencherEEnviarFormulario(itemData, unidadeCompleta);
        });
        
        modalResultados.hide();
        // Recarrega a página após um pequeno delay para mostrar os novos itens na lista
        setTimeout(() => window.location.reload(), 1000);
    });

    // Função auxiliar para criar e submeter um formulário dinamicamente
    function preencherEEnviarFormulario(itemData, unidade) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = window.location.pathname.replace('/pesquisar', '/precos'); // Ajusta a URL de action

        const campos = {
            fonte: 'Painel de Preços',
            valor: parseFloat(itemData.precoUnitario).toFixed(2),
            unidade_medida: unidade,
            data_coleta: itemData.dataResultado.split('T')[0],
            fornecedor_nome: itemData.nomeFornecedor,
            fornecedor_cnpj: itemData.niFornecedor,
            link_evidencia: null // Link não vem da API, pode ser preenchido manualmente depois se necessário
        };

        for (const key in campos) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = campos[key];
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    }
});