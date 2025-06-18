document.addEventListener('DOMContentLoaded', () => {
    const modalDesconsiderar = document.getElementById('modalDesconsiderar');
    if (modalDesconsiderar) {
        modalDesconsiderar.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const processoId = button.getAttribute('data-processo-id');
            const itemId = button.getAttribute('data-item-id');
            const precoId = button.getAttribute('data-preco-id');
            const form = modalDesconsiderar.querySelector('#formDesconsiderar');
            
            const actionUrl = `/processos/${processoId}/itens/${itemId}/precos/${precoId}/desconsiderar`;
            form.setAttribute('action', actionUrl);
        });
    }
});