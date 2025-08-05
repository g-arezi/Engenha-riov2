/**
 * Script para exibir detalhes do ticket ao lado quando clicado
 */
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se estamos na página de lista de tickets
    if (!document.querySelector('.ticket-list')) return;
    
    // Referência para o container de detalhes do ticket
    const ticketDetailContainer = document.querySelector('#ticket-detail-container');
    
    // Adicionar event listeners aos links de tickets
    function setupTicketLinks() {
        document.querySelectorAll('.ticket-item').forEach(ticketLink => {
            ticketLink.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Marcar o ticket atual como ativo
                document.querySelectorAll('.ticket-item').forEach(item => {
                    item.classList.remove('active');
                });
                this.classList.add('active');
                
                // Obter ID do ticket
                const ticketId = this.getAttribute('data-ticket-id');
                
                // Mostrar indicador de carregamento
                ticketDetailContainer.innerHTML = `
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="mt-3">Carregando detalhes do ticket...</p>
                    </div>
                `;
                
                // Log para debug
                console.log('Carregando detalhes para ticket ID:', ticketId);
                
                // Carregar os detalhes do ticket via AJAX com timestamp para evitar cache
                fetch(`/get-ticket-detail.php?id=${encodeURIComponent(ticketId)}&_=${Date.now()}`)
                    .then(response => {
                        console.log('Resposta recebida:', response.status, response.statusText);
                        if (!response.ok) {
                            throw new Error(`Falha ao obter detalhes do ticket: ${response.status} ${response.statusText}`);
                        }
                        return response.text();
                    })
                    .then(html => {
                        console.log('HTML recebido com sucesso, tamanho:', html.length);
                        ticketDetailContainer.innerHTML = html;
                        
                        // Configurar formulário de resposta
                        const replyForm = ticketDetailContainer.querySelector('.reply-form');
                        if (replyForm) {
                            replyForm.addEventListener('submit', function(e) {
                                e.preventDefault();
                                const formData = new FormData(this);
                                
                                fetch('/reply-ticket.php?id=' + encodeURIComponent(ticketId) + '&ajax=1', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // Recarregar os detalhes do ticket para mostrar a nova resposta
                                        loadTicketDetail(ticketId);
                                        // Limpar o formulário
                                        replyForm.reset();
                                    } else {
                                        alert('Erro ao enviar resposta: ' + data.message);
                                    }
                                })
                                .catch(error => {
                                    console.error('Erro ao enviar resposta:', error);
                                    alert('Ocorreu um erro ao enviar sua resposta.');
                                });
                            });
                        }
                        
                        // Configurar botões de ações
                        setupStatusButtons();
                    })
                    .catch(error => {
                        console.error('Erro ao carregar detalhes do ticket:', error);
                        ticketDetailContainer.innerHTML = `
                            <div class="alert alert-danger m-3" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Erro ao carregar detalhes do ticket</strong>
                                <p class="mb-0 mt-2">Mensagem de erro: ${error.message}</p>
                                <p class="mb-0">ID do ticket: ${ticketId}</p>
                                <hr>
                                <p class="mb-0">
                                    <button class="btn btn-sm btn-outline-danger" onclick="window.location.reload()">
                                        <i class="fas fa-sync me-1"></i> Recarregar Página
                                    </button>
                                </p>
                            </div>
                        `;
                    });
            });
        });
    }
    
    // Função para carregar detalhes do ticket
    function loadTicketDetail(ticketId) {
        console.log('Recarregando detalhes para ticket ID:', ticketId);
        
        fetch(`/get-ticket-detail.php?id=${encodeURIComponent(ticketId)}&_=${Date.now()}`)
            .then(response => {
                console.log('Resposta recebida:', response.status, response.statusText);
                if (!response.ok) {
                    throw new Error(`Falha ao obter detalhes do ticket: ${response.status} ${response.statusText}`);
                }
                return response.text();
            })
            .then(html => {
                console.log('HTML recebido com sucesso, tamanho:', html.length);
                ticketDetailContainer.innerHTML = html;
                setupStatusButtons();
            })
            .catch(error => {
                console.error('Erro ao recarregar detalhes do ticket:', error);
                ticketDetailContainer.innerHTML = `
                    <div class="alert alert-danger m-3" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Erro ao recarregar detalhes do ticket</strong>
                        <p class="mb-0 mt-2">Mensagem de erro: ${error.message}</p>
                        <p class="mb-0">ID do ticket: ${ticketId}</p>
                    </div>
                `;
            });
    }
    
    // Configurar botões de ação de status
    function setupStatusButtons() {
        document.querySelectorAll('.status-action-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const ticketId = this.getAttribute('data-ticket-id');
                const status = this.getAttribute('data-status');
                
                if (!confirm(`Deseja alterar o status do ticket para ${status}?`)) {
                    return;
                }
                
                fetch("/update-ticket-status.php?id=" + ticketId, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ status: status })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Status atualizado com sucesso!");
                        loadTicketDetail(ticketId);
                        // Atualizar a lista de tickets também
                        if (typeof updateTicketsList === 'function') {
                            updateTicketsList();
                        }
                    } else {
                        alert("Erro ao atualizar status: " + data.message);
                    }
                })
                .catch(error => {
                    alert("Erro ao processar solicitação: " + error);
                });
            });
        });
    }
    
    // Configurar links iniciais
    setupTicketLinks();
    
    // Reconfigurar links após cada atualização da lista
    if (typeof window.updateTicketsList !== 'function') {
        window.updateTicketsList = function() {
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
                            
                            // Reaplicar a filtragem atual
                            const activeTab = document.querySelector('.support-tab.active');
                            if (activeTab) {
                                const tabType = activeTab.getAttribute('data-tab');
                                filterTickets(tabType);
                            }
                            
                            // Reconfigurar links após atualização
                            setupTicketLinks();
                            
                            // Restaurar seleção atual se existir
                            const currentActive = document.querySelector('.ticket-item.active');
                            if (currentActive) {
                                const ticketId = currentActive.getAttribute('data-ticket-id');
                                const updatedItem = document.querySelector(`.ticket-item[data-ticket-id="${ticketId}"]`);
                                if (updatedItem) {
                                    updatedItem.classList.add('active');
                                }
                            }
                            
                            console.log('Lista de tickets atualizada com sucesso');
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro ao atualizar lista de tickets:', error);
                });
        };
        
        // Atualizar a cada 5 segundos
        setInterval(window.updateTicketsList, 5000);
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
});
