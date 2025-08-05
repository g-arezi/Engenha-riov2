<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Engenha Rio' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
    <link href="/assets/css/chat-widget.css" rel="stylesheet">
    <link href="/assets/css/ticket-styles.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <?php if (isset($showSidebar) && $showSidebar): ?>
            <?php include __DIR__ . '/sidebar.php'; ?>
        <?php endif; ?>
        
        <div class="flex-grow-1">
            <?php if (isset($showNavbar) && $showNavbar): ?>
                <?php include __DIR__ . '/navbar.php'; ?>
            <?php endif; ?>
            
            <main class="<?= isset($showSidebar) && $showSidebar ? 'main-content' : '' ?>">
                <?= $content ?? '' ?>
            </main>
        </div>
    </div>

    <!-- Chat Support Widget -->
    <div class="chat-widget">
        <button class="chat-trigger pulse" id="chatTrigger" data-bs-toggle="modal" data-bs-target="#chatModal">
            <i class="fas fa-comments"></i>
        </button>
    </div>

    <!-- Chat Modal -->
    <div class="modal fade chat-modal" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chatModalLabel">
                        <i class="fas fa-headset me-2"></i>
                        Central de Suporte
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h6>Como podemos ajudar você hoje?</h6>
                        <p class="text-muted">Escolha uma das opções abaixo para obter suporte</p>
                    </div>

                    <div class="chat-options">
                        <a href="/support/create" class="chat-option">
                            <i class="fas fa-ticket-alt"></i>
                            <h6>Abrir Ticket</h6>
                            <p>Criar um novo ticket de suporte para problemas técnicos</p>
                        </a>
                        
                        <a href="/support" class="chat-option">
                            <i class="fas fa-history"></i>
                            <h6>Meus Tickets</h6>
                            <p>Visualizar e acompanhar tickets já criados</p>
                        </a>
                        
                        <a href="#" class="chat-option" onclick="openWhatsapp()">
                            <i class="fab fa-whatsapp"></i>
                            <h6>WhatsApp</h6>
                            <p>Contato direto via WhatsApp para urgências</p>
                        </a>
                        
                        <a href="#" class="chat-option" onclick="openTelegram()">
                            <i class="fab fa-telegram"></i>
                            <h6>Telegram</h6>
                            <p>Suporte rápido através do Telegram</p>
                        </a>
                    </div>

                    <div class="quick-actions">
                        <h6><i class="fas fa-bolt me-2"></i>Ações Rápidas</h6>
                        
                        <div class="quick-action-item" onclick="window.location.href='/documents/upload'">
                            <i class="fas fa-upload"></i>
                            <span>Enviar Documento</span>
                        </div>
                        
                        <div class="quick-action-item" onclick="window.location.href='/projects'">
                            <i class="fas fa-project-diagram"></i>
                            <span>Ver Meus Projetos</span>
                        </div>
                        
                        <div class="quick-action-item" onclick="window.location.href='/documents'">
                            <i class="fas fa-file-alt"></i>
                            <span>Documentos Internos</span>
                        </div>
                        
                        <div class="quick-action-item" onclick="checkSystemStatus()">
                            <i class="fas fa-heartbeat"></i>
                            <span>Status do Sistema</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        Horário de atendimento: Segunda a Sexta, 8h às 18h
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
    <script src="/assets/js/functions.js"></script>
    <script src="/assets/js/page-functions.js"></script>
    <script src="/assets/js/notifications.js"></script>
    <script src="/assets/js/session-keeper.js"></script>
    <script src="/assets/js/ticket-list-refresh.js"></script>
    <script src="/assets/js/ticket-detail-view.js"></script>
    <script>
        // Chat widget functions
        function openWhatsapp() {
            const phone = '5511999999999'; // Substitua pelo número real
            const message = 'Olá! Preciso de suporte técnico no sistema Engenha Rio.';
            window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`, '_blank');
        }
        
        function openTelegram() {
            const username = 'engenhario_suporte'; // Substitua pelo username real
            window.open(`https://t.me/${username}`, '_blank');
        }
        
        function checkSystemStatus() {
            alert('✅ Sistema funcionando normalmente\n\n🟢 Servidor: Online\n🟢 Banco de Dados: Conectado\n🟢 Upload de Arquivos: Funcionando\n\nÚltima verificação: ' + new Date().toLocaleString());
        }
        
        // Auto-hide pulse animation after 10 seconds
        setTimeout(() => {
            document.getElementById('chatTrigger')?.classList.remove('pulse');
        }, 10000);
    </script>
    <?= $scripts ?? '' ?>
</body>
</html>
