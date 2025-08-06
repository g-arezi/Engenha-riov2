/**
 * Ticket System Utilities
 * Scripts adicionais para melhorar a funcionalidade do sistema de tickets
 */

document.addEventListener('DOMContentLoaded', function() {
    // Adicionar feedback visual para botões de ação
    function setupActionButtonFeedback() {
        // Verificar se estamos na página de tickets
        if (!document.querySelector('.ticket-list, #ticket-detail-container')) return;
        
        // Interceptar envios de formulário para mostrar feedback visual
        document.body.addEventListener('submit', function(e) {
            const form = e.target;
            
            // Verificar se é um formulário de ticket
            if (form.classList.contains('reply-form')) {
                const submitBtn = form.querySelector('[type="submit"]');
                if (submitBtn) {
                    // Salvar texto original do botão em um atributo de dados
                    submitBtn.setAttribute('data-original-text', submitBtn.innerHTML);
                }
            }
        });
        
        // Função para restaurar botões após ação AJAX
        window.restoreSubmitButton = function(buttonElement) {
            if (!buttonElement) return;
            
            // Verificar se temos o texto original salvo
            const originalText = buttonElement.getAttribute('data-original-text');
            
            if (originalText) {
                buttonElement.innerHTML = originalText;
            } else {
                // Fallback para texto padrão caso não tenha salvo o original
                buttonElement.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Enviar';
            }
            
            // Reativar botão
            buttonElement.disabled = false;
        };
    }
    
    // Inicializar
    setupActionButtonFeedback();
});
