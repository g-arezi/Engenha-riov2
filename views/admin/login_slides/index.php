<?php
// List of login slides
$slides = $slides ?? [];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gerenciar Slides da Tela de Login</h1>
        <a href="/admin/login-slides/create" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Adicionar Novo Slide
        </a>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success" role="alert">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger" role="alert">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
    <?php endif; ?>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Slides Disponíveis</h6>
        </div>
        <div class="card-body">
            <p class="mb-3">
                Arraste os slides para reordenar. Os slides serão exibidos na tela de login na ordem definida.
                Apenas slides ativos serão exibidos.
            </p>
            
            <?php if (empty($slides)): ?>
            <div class="alert alert-info">
                Nenhum slide cadastrado. Adicione um novo slide.
            </div>
            <?php else: ?>
            <ul class="list-group slides-sortable" id="slidesContainer">
                <?php foreach ($slides as $slide): ?>
                <li class="list-group-item d-flex align-items-center" data-id="<?= $slide['id'] ?>">
                    <div class="handle mr-3 cursor-move">
                        <i class="fas fa-grip-lines"></i>
                    </div>
                    
                    <div class="slide-preview mr-3">
                        <?php if ($slide['type'] === 'image'): ?>
                            <img src="<?= $slide['url'] ?>" alt="Preview" width="80" height="60" class="img-thumbnail">
                        <?php else: ?>
                            <div class="color-preview" style="width: 80px; height: 60px; background-color: <?= $slide['url'] ?>"></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="slide-info flex-grow-1">
                        <h6 class="mb-0"><?= htmlspecialchars($slide['title']) ?></h6>
                        <small class="text-muted">
                            <?= $slide['type'] === 'image' ? 'Imagem' : 'Cor de Fundo' ?> | 
                            Ordem: <?= $slide['order'] ?>
                        </small>
                        <p class="mb-0 small text-truncate">
                            <?= htmlspecialchars($slide['description']) ?>
                        </p>
                    </div>
                    
                    <div class="slide-actions">
                        <button class="btn btn-sm btn-toggle-status mr-1" data-id="<?= $slide['id'] ?>" 
                                data-active="<?= $slide['active'] ? '1' : '0' ?>">
                            <i class="fas <?= $slide['active'] ? 'fa-toggle-on text-success' : 'fa-toggle-off text-secondary' ?>"></i>
                        </button>
                        
                        <a href="/admin/login-slides/edit/<?= $slide['id'] ?>" class="btn btn-sm btn-info mr-1">
                            <i class="fas fa-edit"></i>
                        </a>
                        
                        <button class="btn btn-sm btn-danger btn-delete-slide" data-id="<?= $slide['id'] ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Visualização da Tela de Login</h6>
        </div>
        <div class="card-body">
            <p>Abaixo está uma prévia de como ficará a tela de login com os slides configurados:</p>
            <div class="login-preview-container">
                <div class="login-preview">
                    <div class="login-slides-preview" id="loginSlidesPreview">
                        <?php foreach ($slides as $slide): ?>
                            <?php if ($slide['active']): ?>
                                <div class="login-slide-item">
                                    <?php if ($slide['type'] === 'image'): ?>
                                        <div class="login-slide-image" style="background-image: url('<?= $slide['url'] ?>')">
                                            <div class="login-slide-content">
                                                <h3><?= htmlspecialchars($slide['title']) ?></h3>
                                                <p><?= htmlspecialchars($slide['description']) ?></p>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="login-slide-color" style="background-color: <?= $slide['url'] ?>">
                                            <div class="login-slide-content">
                                                <h3><?= htmlspecialchars($slide['title']) ?></h3>
                                                <p><?= htmlspecialchars($slide['description']) ?></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="login-form-preview">
                        <div class="login-logo-preview">
                            <img src="/assets/images/engenhario-logo.png" alt="Logo" height="50">
                            <h5>Engenha Rio</h5>
                        </div>
                        <div class="login-fields-preview">
                            <div class="form-preview-field"></div>
                            <div class="form-preview-field"></div>
                            <div class="form-preview-button"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Slide Modal -->
<div class="modal fade" id="deleteSlideModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Tem certeza que deseja excluir este slide? Esta ação não pode ser desfeita.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <form id="deleteSlideForm" method="POST">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .cursor-move { cursor: move; }
    .slide-preview img, .color-preview { object-fit: cover; }
    
    .login-preview-container {
        padding: 20px;
        background-color: #f8f9fc;
        border-radius: 5px;
    }
    
    .login-preview {
        display: flex;
        max-width: 900px;
        height: 400px;
        margin: 0 auto;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        overflow: hidden;
        flex-direction: row-reverse; /* Inverte a ordem para mostrar o formulário à direita */
    }
    
    .login-slides-preview {
        flex: 1;
        position: relative;
        overflow: hidden;
    }
    
    .login-slide-item {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 1s ease;
    }
    
    .login-slide-item:first-child {
        opacity: 1;
    }
    
    .login-slide-image, .login-slide-color {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .login-slide-content {
        padding: 20px;
        text-align: center;
        color: white;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.6);
        background-color: rgba(0, 0, 0, 0.3);
        border-radius: 10px;
        max-width: 80%;
    }
    
    .login-form-preview {
        width: 350px;
        background-color: white;
        padding: 30px;
        display: flex;
        flex-direction: column;
    }
    
    .login-logo-preview {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .login-logo-preview h5 {
        margin-top: 10px;
        color: #333;
    }
    
    .login-fields-preview {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .form-preview-field {
        height: 40px;
        background-color: #f0f0f0;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    
    .form-preview-button {
        height: 40px;
        background-color: #4e73df;
        border-radius: 5px;
        margin-top: 10px;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
$(document).ready(function() {
    // Sortable for reordering slides
    const container = document.getElementById('slidesContainer');
    if (container) {
        new Sortable(container, {
            animation: 150,
            ghostClass: 'bg-light',
            handle: '.handle',
            onEnd: function() {
                // Get new order
                const slideIds = [];
                $('#slidesContainer li').each(function() {
                    slideIds.push($(this).data('id'));
                });
                
                // Send new order to server using jQuery AJAX
                $.ajax({
                    url: '/admin/login-slides/order',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ slides: slideIds }),
                    success: function(data) {
                        if (data.success) {
                            console.log('Ordem atualizada com sucesso');
                            // Reload page to refresh the order numbers
                            location.reload();
                        } else {
                            alert(data.message || 'Erro ao atualizar a ordem dos slides');
                        }
                    },
                    error: function() {
                        alert('Erro ao comunicar com o servidor');
                    }
                });
            }
        });
    }
    
    // Toggle status buttons
    $('.btn-toggle-status').on('click', function() {
        const slideId = $(this).data('id');
        const isActive = $(this).data('active') === 1;
        const $icon = $(this).find('i');
        
        $.ajax({
            url: `/admin/login-slides/toggle/${slideId}`,
            method: 'POST',
            contentType: 'application/json',
            success: function(data) {
                if (data.success) {
                    // Toggle the button appearance
                    if (data.active) {
                        $icon.removeClass('fa-toggle-off text-secondary').addClass('fa-toggle-on text-success');
                        $(this).data('active', 1);
                    } else {
                        $icon.removeClass('fa-toggle-on text-success').addClass('fa-toggle-off text-secondary');
                        $(this).data('active', 0);
                    }
                    
                    // Reload page to update the preview
                    location.reload();
                } else {
                    alert(data.message || 'Erro ao alterar status do slide');
                }
            },
            error: function() {
                alert('Erro ao comunicar com o servidor');
            }
        });
    });
    
    // Delete slide buttons
    $('.btn-delete-slide').on('click', function() {
        const slideId = $(this).data('id');
        $('#deleteSlideForm').attr('action', `/admin/login-slides/delete/${slideId}`);
        
        // Usar jQuery para manipular o modal (mais compatível)
        $('#deleteSlideModal').modal('show');
    });
    
    // Login slides preview animation
    const $slideItems = $('.login-slide-item');
    if ($slideItems.length > 1) {
        let currentSlide = 0;
        
        setInterval(function() {
            $($slideItems[currentSlide]).css('opacity', 0);
            currentSlide = (currentSlide + 1) % $slideItems.length;
            $($slideItems[currentSlide]).css('opacity', 1);
        }, 5000);
    }
});
</script>
