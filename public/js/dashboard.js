document.addEventListener('DOMContentLoaded', () => {
    // Gráfico de Status (Pizza)
    const canvasStatus = document.getElementById('graficoStatus');
    if (canvasStatus) {
        const dadosStatus = JSON.parse(canvasStatus.dataset.dados);
        new Chart(canvasStatus, {
            type: 'doughnut',
            data: {
                labels: dadosStatus.map(d => d.status),
                datasets: [{
                    label: 'Processos',
                    data: dadosStatus.map(d => d.total),
                    backgroundColor: ['#6c757d', '#ffc107', '#198754', '#dc3545', '#0dcaf0'],
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    // Gráfico de Tipo (Barras)
    const canvasTipo = document.getElementById('graficoTipo');
    if (canvasTipo) {
        const dadosTipo = JSON.parse(canvasTipo.dataset.dados);
        new Chart(canvasTipo, {
            type: 'bar',
            data: {
                labels: dadosTipo.map(d => d.tipo_contratacao),
                datasets: [{
                    label: 'Total de Processos',
                    data: dadosTipo.map(d => d.total),
                    backgroundColor: '#0d6efd',
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // Gráfico por Agentes (Barras Horizontais)
    const canvasAgentes = document.getElementById('graficoAgentes');
    if (canvasAgentes) {
        const dadosAgentes = JSON.parse(canvasAgentes.dataset.dados);
        new Chart(canvasAgentes, {
            type: 'bar',
            data: {
                labels: dadosAgentes.map(d => d.agente_responsavel),
                datasets: [{
                    label: 'Nº de Processos',
                    data: dadosAgentes.map(d => d.total),
                    backgroundColor: '#fd7e14',
                }]
            },
            options: {
                indexAxis: 'y', // Isso transforma o gráfico em barras horizontais
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }
});