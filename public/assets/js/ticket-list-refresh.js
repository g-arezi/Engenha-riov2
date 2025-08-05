/**
 * Script para atualização automática da lista de tickets
 */
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se estamos na página de lista de tickets
    if (!document.querySelector('.ticket-list')) return;
    
    // Função para atualizar a lista de tickets
    function updateTicketsList() {
        fetch(`/get-ticket-list-simple.php?timestamp=${Date.now()}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Falha ao obter tickets');
                }
                return response.text();
            })
            .then(html => {
                // Extrair o conteúdo da lista de tickets do HTML
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                const newTicketList = tempDiv.querySelector('.ticket-list');
                
                if (newTicketList) {
                    // Atualizar o conteúdo da lista de tickets
                    const currentTicketList = document.querySelector('.ticket-list');
                    if (currentTicketList) {
                        currentTicketList.innerHTML = newTicketList.innerHTML;
                        
                        // Certificar-se de que todos os links estão com o caminho correto
                        document.querySelectorAll('.ticket-item').forEach(ticketLink => {
                            const ticketId = ticketLink.getAttribute('data-ticket-id');
                            if (ticketId) {
                                // Garantir que todos os links usam o ticket-simple-view.php
                                ticketLink.href = `/ticket-simple-view.php?id=${ticketId}`;
                                console.log(`Fixed ticket link for ID ${ticketId}`);
                            }
                        });
                        
                        // Reaplicar a filtragem atual
                        const activeTab = document.querySelector('.support-tab.active');
                        if (activeTab) {
                            const tabType = activeTab.getAttribute('data-tab');
                            filterTickets(tabType);
                        }
                        
                        console.log('Lista de tickets atualizada com sucesso');
                    }
                }
            })
            .catch(error => {
                console.error('Erro ao atualizar lista de tickets:', error);
            });
    }
    
    // Função para filtrar tickets
    function filterTickets(tabType) {
        document.querySelectorAll('.ticket-item').forEach(item => {
            // Garantir que cada ticket está visível antes da filtragem
            item.style.display = 'block';
            
            const status = item.getAttribute('data-status');
            
            // Aplicar filtragem apenas se o status corresponder ao critério
            if (tabType === 'open' && status === 'fechado') {
                item.style.display = 'none';
            }
            else if (tabType === 'history' && status !== 'fechado') {
                item.style.display = 'none';
            }
        });
    }
    
    // Atualizar a cada 5 segundos
    setInterval(updateTicketsList, 5000);
});
