// Função melhorada para download
window.downloadDocument = function(documentId) {
    console.log('Iniciando download do documento ID:', documentId);
    
    try {
        // Mostrar indicador de carregamento
        if (typeof showAlert === 'function') {
            showAlert('Iniciando download...', 'info');
        } else {
            alert('Iniciando download do documento...');
        }
        
        // Verificar se o ID é válido
        if (!documentId) {
            throw new Error('ID do documento inválido');
        }
        
        // Criar um iframe oculto para o download
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        document.body.appendChild(iframe);
        
        // Configurar evento para remoção do iframe após o carregamento
        iframe.addEventListener('load', function() {
            setTimeout(function() {
                document.body.removeChild(iframe);
            }, 1000);
            
            // Verificar se o download foi bem-sucedido (verificação básica)
            if (typeof showAlert === 'function') {
                showAlert('Download iniciado com sucesso', 'success');
            }
        });
        
        // Configurar evento para erro de carregamento
        iframe.addEventListener('error', function() {
            document.body.removeChild(iframe);
            console.error('Erro ao carregar iframe para download');
            
            if (typeof showAlert === 'function') {
                showAlert('Erro ao iniciar download. Tente novamente.', 'error');
            } else {
                alert('Erro ao iniciar download. Tente novamente.');
            }
        });
        
        // Configurar o src do iframe para iniciar o download
        iframe.src = `/documents/project/${documentId}/download`;
        
    } catch (error) {
        console.error('Erro ao iniciar download:', error);
        
        if (typeof showAlert === 'function') {
            showAlert('Erro ao iniciar download: ' + error.message, 'error');
        } else {
            alert('Erro ao iniciar download: ' + error.message);
        }
    }
};
