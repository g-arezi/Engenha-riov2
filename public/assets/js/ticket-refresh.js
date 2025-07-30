/**
 * Script para atualização automática das respostas de ticket
 */
document.addEventListener('DOMContentLoaded', function() {
    // Verifique se estamos na página de visualização de ticket
    const repliesList = document.getElementById('replies-list');
    if (!repliesList) return;
    
    const ticketId = repliesList.dataset.ticketId;
    if (!ticketId) return;
    
    // Adicionar indicador de atualização automática
    const statusIndicator = document.createElement('div');
    statusIndicator.className = 'auto-refresh-status text-center mb-3 mt-2';
    statusIndicator.innerHTML = `
        <small class="text-muted">
            <i class="fas fa-sync-alt fa-spin me-1"></i>
            Atualizando automaticamente...
        </small>
    `;
    repliesList.parentNode.insertBefore(statusIndicator, repliesList.nextSibling);
    
    // Função para atualizar as respostas
    function updateReplies() {
        // Obter todas as respostas atuais para verificar o que é novo
        const currentReplies = Array.from(document.querySelectorAll('.reply-item')).map(item => item.dataset.replyId);
        
        // Fazer uma requisição AJAX para obter as respostas atualizadas
        console.log('Fazendo requisição para:', `/get-ticket-replies.php?id=${ticketId}&timestamp=${Date.now()}`);
        
        fetch(`/get-ticket-replies.php?id=${ticketId}&timestamp=${Date.now()}`)
            .then(response => {
                console.log('Resposta recebida:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Dados recebidos:', data);
                if (data.success) {
                    // Se não há respostas atuais mas agora temos respostas, limpe a mensagem "Nenhuma resposta ainda"
                    if (currentReplies.length === 0 && data.replies.length > 0) {
                        const noRepliesElement = repliesList.querySelector('.no-replies');
                        if (noRepliesElement) {
                            noRepliesElement.remove();
                        }
                    }
                    
                    // Adicionar novas respostas
                    data.replies.forEach(reply => {
                        // Verifique se a resposta já existe
                        if (!currentReplies.includes(reply.id)) {
                            // Criar elemento de resposta
                            const replyElement = document.createElement('div');
                            replyElement.className = 'reply-item mb-3';
                            replyElement.dataset.replyId = reply.id;
                            
                            const isStaff = reply.is_staff ? `<span class="badge bg-primary ms-1">Equipe</span>` : '';
                            const userName = reply.user_name || reply.user_id;
                            const formattedDate = new Date(reply.created_at).toLocaleString('pt-BR');
                            
                            // Verificar se há um anexo
                            let attachmentHtml = '';
                            if (reply.attachment) {
                                const attachmentName = reply.attachment_name || 'Imagem anexada';
                                attachmentHtml = `
                                    <div class="reply-attachment mt-2">
                                        <a href="${reply.attachment}" target="_blank" class="d-block">
                                            <img src="${reply.attachment}" alt="Imagem anexada" class="img-fluid img-thumbnail" style="max-height: 200px;">
                                        </a>
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-paperclip me-1"></i>
                                            ${attachmentName}
                                        </small>
                                    </div>
                                `;
                            }
                            
                            replyElement.innerHTML = `
                                <div class="d-flex">
                                    <img src="/assets/images/avatar-default.svg" alt="Avatar" class="rounded-circle me-3" width="32" height="32">
                                    <div class="flex-grow-1">
                                        <div class="reply-header d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <strong>${userName}</strong>
                                                ${isStaff}
                                            </div>
                                            <small class="text-muted">${formattedDate}</small>
                                        </div>
                                        <div class="reply-content">
                                            ${reply.message.replace(/\n/g, '<br>')}
                                            ${attachmentHtml}
                                        </div>
                                    </div>
                                </div>
                            `;
                            
                            // Adicionar ao DOM
                            repliesList.appendChild(replyElement);
                            
                            // Adicionar linha horizontal
                            const hr = document.createElement('hr');
                            repliesList.appendChild(hr);
                            
                            // Adicionar à lista de respostas atuais
                            currentReplies.push(reply.id);
                            
                            // Adicionar classe para animação de nova mensagem
                            replyElement.classList.add('new-reply');
                            setTimeout(() => {
                                replyElement.classList.remove('new-reply');
                            }, 3000);
                            
                            // Mostrar notificação na página
                            showNotification(`Nova resposta de ${userName}`);
                            
                            // Reproduzir um som de notificação
                            playNotificationSound();
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao atualizar respostas:', error);
            });
    }
    
    // Função para reproduzir som de notificação
    function playNotificationSound() {
        // Criar elemento de áudio
        const audio = new Audio('/assets/sounds/notification.mp3');
        audio.volume = 0.5;
        audio.play().catch(e => {
            // Silenciar erros de reprodução automática
            console.log('Não foi possível reproduzir o som de notificação');
        });
    }
    
    // Função para mostrar uma notificação visual
    function showNotification(message) {
        // Criar elemento de notificação
        const notification = document.createElement('div');
        notification.className = 'toast align-items-center text-white bg-primary border-0 position-fixed bottom-0 end-0 m-3';
        notification.setAttribute('role', 'alert');
        notification.setAttribute('aria-live', 'assertive');
        notification.setAttribute('aria-atomic', 'true');
        
        notification.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-bell me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        // Adicionar ao DOM
        document.body.appendChild(notification);
        
        // Mostrar notificação usando Bootstrap
        const toast = new bootstrap.Toast(notification, { delay: 5000 });
        toast.show();
        
        // Remover do DOM depois de fechado
        notification.addEventListener('hidden.bs.toast', function() {
            document.body.removeChild(notification);
        });
    }
    
    // Executar uma atualização imediata para verificar se está funcionando
    console.log('Iniciando atualização automática de respostas para o ticket ID:', ticketId);
    updateReplies();
    
    // Atualizar a cada 10 segundos
    const intervalId = setInterval(() => {
        console.log('Verificando novas respostas...');
        updateReplies();
    }, 10000);
    
    // Limpar intervalo quando o usuário sair da página
    window.addEventListener('beforeunload', function() {
        clearInterval(intervalId);
    });
});
