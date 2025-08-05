<?php
$title = 'Gerenciar Slides de Login - Engenha Rio';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Gerenciar Slides de Login</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
                        <li class="breadcrumb-item active">Slides de Login</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Slides Existentes</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addSlideModal">
                                    <i class="fas fa-plus"></i> Adicionar Slide
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px">ID</th>
                                            <th>Visualização</th>
                                            <th>Título</th>
                                            <th>Tipo</th>
                                            <th>Status</th>
                                            <th style="width: 150px">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($slides as $slide): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($slide['id']) ?></td>
                                            <td>
                                                <?php if ($slide['type'] === 'image'): ?>
                                                    <img src="<?= htmlspecialchars($slide['url']) ?>" alt="Slide Preview" class="img-thumbnail" style="max-height: 80px;">
                                                <?php else: ?>
                                                    <div class="color-box" style="background-color: <?= htmlspecialchars($slide['url']) ?>; height: 80px; width: 100px; border-radius: 4px;"></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($slide['title']) ?></td>
                                            <td><?= $slide['type'] === 'image' ? 'Imagem' : 'Cor' ?></td>
                                            <td>
                                                <span class="badge badge-<?= $slide['active'] ? 'success' : 'secondary' ?>">
                                                    <?= $slide['active'] ? 'Ativo' : 'Inativo' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm edit-slide" 
                                                    data-id="<?= htmlspecialchars($slide['id']) ?>"
                                                    data-title="<?= htmlspecialchars($slide['title']) ?>"
                                                    data-description="<?= htmlspecialchars($slide['description']) ?>"
                                                    data-type="<?= htmlspecialchars($slide['type']) ?>"
                                                    data-url="<?= htmlspecialchars($slide['url']) ?>"
                                                    data-active="<?= $slide['active'] ? '1' : '0' ?>"
                                                    data-toggle="modal" data-target="#editSlideModal">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm delete-slide" 
                                                    data-id="<?= htmlspecialchars($slide['id']) ?>"
                                                    data-toggle="modal" data-target="#deleteSlideModal">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        
                                        <?php if (empty($slides)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Nenhum slide encontrado</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Pré-visualização</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="login-preview">
                                        <div class="login-form-preview">
                                            <div class="login-form-container">
                                                <img src="/assets/images/engenhario-logo.png" alt="Engenha Rio" class="logo-preview">
                                                <div class="form-preview-placeholder"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="login-preview-container">
                                            <div class="slides-section">
                                                <?php if (!empty($slides)): ?>
                                                    <?php $activeSlides = array_filter($slides, function($s) { return $s['active']; }); ?>
                                                    <?php if (!empty($activeSlides)): ?>
                                                        <?php $randomSlide = $activeSlides[array_rand($activeSlides)]; ?>
                                                        
                                                        <?php if ($randomSlide['type'] === 'image'): ?>
                                                            <div class="preview-slide" style="background-image: url('<?= htmlspecialchars($randomSlide['url']) ?>')">
                                                        <?php else: ?>
                                                            <div class="preview-slide" style="background-color: <?= htmlspecialchars($randomSlide['url']) ?>">
                                                        <?php endif; ?>
                                                            
                                                            <div class="slide-content">
                                                                <h3><?= htmlspecialchars($randomSlide['title']) ?></h3>
                                                                <p><?= htmlspecialchars($randomSlide['description']) ?></p>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="preview-slide" style="background-color: #2c3e50">
                                                            <div class="slide-content">
                                                                <h3>Nenhum slide ativo</h3>
                                                                <p>Ative pelo menos um slide para visualizá-lo aqui.</p>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="preview-slide" style="background-color: #2c3e50">
                                                        <div class="slide-content">
                                                            <h3>Sem slides disponíveis</h3>
                                                            <p>Adicione slides para visualizá-los aqui.</p>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="alert alert-info">
                                        <h5><i class="fas fa-info-circle"></i> Como funciona:</h5>
                                        <p>Os slides ativos são exibidos aleatoriamente na página de login. Cada vez que um usuário acessa a página, um slide diferente pode ser mostrado.</p>
                                        <hr>
                                        <p>Você pode criar slides com:</p>
                                        <ul>
                                            <li>Imagens de fundo</li>
                                            <li>Cores sólidas</li>
                                        </ul>
                                        <p>Cada slide possui um título e uma descrição que serão exibidos sobre o fundo.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Adicionar Slide -->
<div class="modal fade" id="addSlideModal" tabindex="-1" role="dialog" aria-labelledby="addSlideModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="addSlideForm" action="/admin/login-slides/add" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSlideModalLabel">Adicionar Novo Slide</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="addTitle">Título</label>
                        <input type="text" class="form-control" id="addTitle" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="addDescription">Descrição</label>
                        <textarea class="form-control" id="addDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Tipo de Slide</label>
                        <div class="slide-type-selector">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="addTypeImage" value="image" checked>
                                <label class="form-check-label" for="addTypeImage">Imagem</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="addTypeColor" value="color">
                                <label class="form-check-label" for="addTypeColor">Cor Sólida</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group image-upload-section">
                        <label for="addImage">Upload de Imagem</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="addImage" name="image" accept="image/*">
                            <label class="custom-file-label" for="addImage">Escolher arquivo</label>
                        </div>
                        <small class="form-text text-muted">Recomendado: imagens com resolução de 1200x800 pixels ou proporção similar.</small>
                    </div>
                    <div class="form-group color-selector-section" style="display:none;">
                        <label for="addColor">Cor de Fundo</label>
                        <input type="color" class="form-control" id="addColor" name="color" value="#2c3e50">
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="addActive" name="active" checked>
                            <label class="form-check-label" for="addActive">Ativar este slide</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Slide -->
<div class="modal fade" id="editSlideModal" tabindex="-1" role="dialog" aria-labelledby="editSlideModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editSlideForm" action="/admin/login-slides/update" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="editId" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSlideModalLabel">Editar Slide</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editTitle">Título</label>
                        <input type="text" class="form-control" id="editTitle" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="editDescription">Descrição</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Tipo de Slide</label>
                        <div class="slide-type-selector">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="editTypeImage" value="image">
                                <label class="form-check-label" for="editTypeImage">Imagem</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="editTypeColor" value="color">
                                <label class="form-check-label" for="editTypeColor">Cor Sólida</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group edit-image-preview">
                        <label>Imagem Atual</label>
                        <div class="current-image-preview mb-2"></div>
                    </div>
                    <div class="form-group edit-image-upload-section">
                        <label for="editImage">Alterar Imagem (opcional)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="editImage" name="image" accept="image/*">
                            <label class="custom-file-label" for="editImage">Escolher arquivo</label>
                        </div>
                        <small class="form-text text-muted">Deixe em branco para manter a imagem atual.</small>
                    </div>
                    <div class="form-group edit-color-selector-section" style="display:none;">
                        <label for="editColor">Cor de Fundo</label>
                        <input type="color" class="form-control" id="editColor" name="color" value="#2c3e50">
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="editActive" name="active">
                            <label class="form-check-label" for="editActive">Ativar este slide</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Excluir Slide -->
<div class="modal fade" id="deleteSlideModal" tabindex="-1" role="dialog" aria-labelledby="deleteSlideModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="deleteSlideForm" action="/admin/login-slides/delete" method="POST">
                <input type="hidden" id="deleteId" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteSlideModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza de que deseja excluir este slide? Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .login-preview {
        border: 1px solid #ddd;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: row-reverse; /* Inverte a ordem para mostrar o formulário à esquerda */
    }
    
    .login-preview-container {
        display: flex;
        height: 400px;
    }
    
    .slides-section {
        flex: 1;
        position: relative;
    }
    
    .preview-slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .preview-slide::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.3);
        z-index: 1;
    }
    
    .slide-content {
        text-align: center;
        color: white;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.6);
        max-width: 80%;
        z-index: 2;
    }
    
    .login-form-preview {
        width: 250px;
        background: white;
        padding: 20px;
    }
    
    .login-form-container {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .logo-preview {
        max-width: 120px;
        margin-bottom: 20px;
    }
    
    .form-preview-placeholder {
        background: #f5f5f5;
        border-radius: 8px;
        width: 100%;
        height: 300px;
    }
    
    .current-image-preview img {
        max-width: 100%;
        max-height: 150px;
        border-radius: 5px;
    }
    
    .color-box {
        box-shadow: 0 0 5px rgba(0,0,0,0.2);
    }
</style>

<script>
$(document).ready(function() {
    // Toggle entre os tipos de slide no formulário de adicionar
    $('input[name="type"]').change(function() {
        if ($(this).val() === 'image') {
            $('.image-upload-section').show();
            $('.color-selector-section').hide();
        } else {
            $('.image-upload-section').hide();
            $('.color-selector-section').show();
        }
    });
    
    // Exibir nome do arquivo selecionado
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
    
    // Configurar modal de edição
    $('.edit-slide').click(function() {
        var id = $(this).data('id');
        var title = $(this).data('title');
        var description = $(this).data('description');
        var type = $(this).data('type');
        var url = $(this).data('url');
        var active = $(this).data('active') == 1;
        
        $('#editId').val(id);
        $('#editTitle').val(title);
        $('#editDescription').val(description);
        
        if (type === 'image') {
            $('#editTypeImage').prop('checked', true);
            $('.edit-image-upload-section').show();
            $('.edit-color-selector-section').hide();
            $('.edit-image-preview').show();
            $('.current-image-preview').html('<img src="' + url + '" class="img-fluid">');
        } else {
            $('#editTypeColor').prop('checked', true);
            $('.edit-image-upload-section').hide();
            $('.edit-color-selector-section').show();
            $('.edit-image-preview').hide();
            $('#editColor').val(url);
        }
        
        $('#editActive').prop('checked', active);
    });
    
    // Toggle entre os tipos de slide no formulário de edição
    $('#editTypeImage, #editTypeColor').change(function() {
        if ($('#editTypeImage').is(':checked')) {
            $('.edit-image-upload-section').show();
            $('.edit-image-preview').show();
            $('.edit-color-selector-section').hide();
        } else {
            $('.edit-image-upload-section').hide();
            $('.edit-image-preview').hide();
            $('.edit-color-selector-section').show();
        }
    });
    
    // Configurar modal de exclusão
    $('.delete-slide').click(function() {
        var id = $(this).data('id');
        $('#deleteId').val(id);
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
