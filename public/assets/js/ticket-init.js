// Inicializa o script de ticket-refresh.js para garantir que seja carregado
document.addEventListener('DOMContentLoaded', function() {
    console.log('Script de inicialização de ticket carregado');
    
    // Verificar se estamos na página de visualização de ticket
    const repliesList = document.getElementById('replies-list');
    if (repliesList) {
        console.log('Encontrado elemento replies-list com ID do ticket:', repliesList.dataset.ticketId);
        
        // Verificar se o script principal foi carregado
        if (typeof updateReplies === 'undefined') {
            console.error('ERRO: Script ticket-refresh.js não foi carregado corretamente!');
            
            // Tentar carregar o script novamente
            const script = document.createElement('script');
            script.src = '/assets/js/ticket-refresh.js?nocache=' + Date.now();
            script.onload = function() {
                console.log('Script ticket-refresh.js carregado manualmente');
            };
            document.head.appendChild(script);
        }
    } else {
        console.log('Elemento replies-list não encontrado - não estamos na página de visualização de ticket');
    }
});
