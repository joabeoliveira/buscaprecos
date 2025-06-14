// A LINHA MAIS IMPORTANTE: garante que o código abaixo só rode
// depois que todo o HTML da página foi carregado pelo navegador.
document.addEventListener('DOMContentLoaded', () => {

    // I. Configuração do Cliente Supabase
    const supabaseUrl = 'https://abuowxogoiqzbmnvszys.supabase.co';
    const supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFidW93eG9nb2lxemJtbnZzenlzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDkyNTcwNTcsImV4cCI6MjA2NDgzMzA1N30.t6b1vtcZhGfOfibwdWKLDUJq2BoRegH5s6P5_OvRwz8';
    const supabase = window.supabase.createClient(supabaseUrl, supabaseKey);

    // II. Seleção dos Elementos do DOM
    const inputBuscaModal = document.getElementById('inputBuscaModal');
    const listaSugestoes = document.getElementById('listaSugestoes');
    const btnLimpar = document.getElementById('btnLimpar');
    const inputCatmatPrincipal = document.getElementById('catmat_input');
    const inputDescricaoPrincipal = document.getElementById('descricao_input');
    const modalElement = document.getElementById('modalBuscaCatmat');
    
    // Apenas cria a instância do Modal se o elemento existir na página
    let modalInstance = null;
    if (modalElement) {
        modalInstance = new bootstrap.Modal(modalElement);
    }

    // III. Funções e Lógica de Busca
    if (btnLimpar) {
        btnLimpar.addEventListener('click', () => {
            inputBuscaModal.value = '';
            listaSugestoes.innerHTML = '';
            inputBuscaModal.focus();
        });
    }

    async function buscarSugestoes(query) {
        if (query.length < 2) {
            listaSugestoes.innerHTML = '';
            return;
        }

        const { data, error } = await supabase.rpc('buscar_itens_similares', {
            texto_busca: query,
            limite: 20,
            offset_val: 0,
        });

        if (error) {
            console.error('Erro na busca:', error);
            listaSugestoes.innerHTML = '<li class="list-group-item text-danger">Erro na busca.</li>';
            return;
        }

        if (!data || data.length === 0) {
            listaSugestoes.innerHTML = '<li class="list-group-item">Nenhuma sugestão encontrada.</li>';
            return;
        }

        listaSugestoes.innerHTML = '';
        data.forEach(item => {
            const li = document.createElement('li');
            li.classList.add('list-group-item', 'd-flex', 'flex-column');
            li.style.cursor = 'pointer';
            li.innerHTML = `
                <div class="item-code">${item.codigo_catmat}</div>
                <div class="item-desc">${item.descricao}</div>
            `;

            li.addEventListener('click', () => {
                if (inputCatmatPrincipal) {
                    inputCatmatPrincipal.value = item.codigo_catmat;
                }
                if (inputDescricaoPrincipal) {
                    inputDescricaoPrincipal.value = item.descricao;
                }

                inputBuscaModal.value = '';
                listaSugestoes.innerHTML = '';

                if (modalInstance) {
                    modalInstance.hide();
                }
            });

            listaSugestoes.appendChild(li);
        });
    }

    if (inputBuscaModal) {
        let timeoutId;
        inputBuscaModal.addEventListener('input', () => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                buscarSugestoes(inputBuscaModal.value);
            }, 300);
        });
    }
// Fim do bloco 'DOMContentLoaded'
});