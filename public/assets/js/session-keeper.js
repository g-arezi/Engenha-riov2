/**
 * Session Keeper - Mantém a sessão PHP ativa com solicitações periódicas
 */
(function() {
    // Intervalo em milissegundos (5 minutos = 300000ms)
    const pingInterval = 300000;
    
    // URL do endpoint de ping
    const pingUrl = '/session-ping.php';
    
    // Função para enviar a solicitação de ping
    function pingSession() {
        console.log('Ping de sessão enviado');
        
        fetch(pingUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Resposta do ping de sessão:', data);
            
            // Se o usuário não estiver mais autenticado, redirecionar para a página de login
            if (!data.authenticated) {
                console.log('Sessão expirada, redirecionando para login');
                window.location.href = '/login';
            }
        })
        .catch(error => {
            console.error('Erro ao enviar ping de sessão:', error);
        });
    }
    
    // Iniciar o ping periódico apenas se o usuário estiver logado
    // (verificar se existe algum elemento que indica usuário logado)
    if (document.querySelector('.user-profile') || document.querySelector('.user-menu')) {
        console.log('Session Keeper iniciado');
        
        // Enviar ping inicial após 1 segundo
        setTimeout(pingSession, 1000);
        
        // Configurar ping periódico
        setInterval(pingSession, pingInterval);
        
        // Também enviar ping quando o usuário interagir com a página
        document.addEventListener('click', function() {
            pingSession();
        }, { passive: true });
    }
})();
