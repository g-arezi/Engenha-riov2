<?php
use App\Core\Auth;

$title = 'Novo Ticket - Engenha Rio';
$showSidebar = true;
$showNavbar = true;

ob_start();
?>

<div class="support-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Novo Ticket de Suporte</h1>
            <p class="text-muted mb-0">Criar um novo ticket de suporte</p>
        </div>
        <a href="/support" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Voltar
        </a>
    </div>
</div>

<!-- Mensagens de Sucesso/Erro -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/support/create" data-validate="true" onsubmit="return validateTicketForm(this)">
                    <input type="hidden" name="debug" value="1">
                    <div class="mb-3">
                        <label for="subject" class="form-label">Assunto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subject" name="subject" required placeholder="Descreva brevemente o problema">
                    </div>
                    
                    <div class="mb-3">
                        <label for="priority" class="form-label">Prioridade</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="baixa">Baixa</option>
                            <option value="media" selected>Média</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="6" required placeholder="Descreva detalhadamente o problema ou solicitação..."></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i>
                            Criar Ticket
                        </button>
                        <a href="/support" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Dicas para um Bom Ticket</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-primary">Seja Específico:</h6>
                    <p class="small text-muted">Descreva exatamente o que aconteceu e quando</p>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-primary">Inclua Detalhes:</h6>
                    <p class="small text-muted">Mensagens de erro, passos para reproduzir o problema</p>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-primary">Prioridade:</h6>
                    <ul class="list-unstyled small text-muted">
                        <li><span class="badge bg-info me-1">Baixa</span> Dúvidas gerais</li>
                        <li><span class="badge bg-warning me-1">Média</span> Problemas que não impedem o trabalho</li>
                        <li><span class="badge bg-danger me-1">Alta</span> Problemas que impedem o trabalho</li>
                        <li><span class="badge bg-dark me-1">Urgente</span> Sistema fora do ar</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validateTicketForm(form) {
    const subject = form.querySelector('#subject').value.trim();
    const description = form.querySelector('#description').value.trim();
    
    if (subject === '') {
        alert('Por favor, preencha o assunto do ticket.');
        return false;
    }
    
    if (description === '') {
        alert('Por favor, preencha a descrição do ticket.');
        return false;
    }
    
    // Debug: log form data
    console.log('Enviando ticket:', {
        subject: subject,
        description: description,
        priority: form.querySelector('#priority').value
    });
    
    return true;
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
