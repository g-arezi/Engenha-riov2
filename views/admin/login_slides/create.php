<?php
// Form to create a new login slide
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Adicionar Novo Slide</h1>
        <a href="/admin/login-slides" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
    </div>
    
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger" role="alert">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
    <?php endif; ?>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informações do Slide</h6>
        </div>
        <div class="card-body">
            <form action="/admin/login-slides/create" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="type">Tipo de Slide:</label>
                    <select class="form-control" id="type" name="type" required>
                        <option value="image">Imagem de Fundo</option>
                        <option value="color">Cor Sólida</option>
                    </select>
                </div>
                
                <div class="form-group" id="imageGroup">
                    <label for="image">Imagem de Fundo:</label>
                    <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                    <small class="form-text text-muted">
                        Tamanho recomendado: 1280x720px. Formatos permitidos: JPG, PNG.
                    </small>
                </div>
                
                <div class="form-group" id="colorGroup" style="display: none;">
                    <label for="color">Cor de Fundo:</label>
                    <input type="color" class="form-control" id="color" name="color" value="#2c3e50">
                </div>
                
                <div class="form-group">
                    <label for="title">Título:</label>
                    <input type="text" class="form-control" id="title" name="title" 
                           placeholder="Título que aparecerá sobre a imagem" maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="description">Descrição:</label>
                    <textarea class="form-control" id="description" name="description" 
                              rows="3" placeholder="Breve descrição ou mensagem" maxlength="255"></textarea>
                </div>
                
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="active" name="active" checked>
                        <label class="custom-control-label" for="active">Slide Ativo</label>
                    </div>
                    <small class="form-text text-muted">
                        Apenas slides ativos serão exibidos na tela de login.
                    </small>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Prévia</h6>
                    </div>
                    <div class="card-body">
                        <div class="slide-preview-container">
                            <div id="previewSlide" class="slide-preview">
                                <div class="slide-content">
                                    <h3 id="previewTitle">Título do Slide</h3>
                                    <p id="previewDescription">Descrição do slide irá aparecer aqui</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Salvar Slide
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .slide-preview-container {
        width: 100%;
        height: 300px;
        overflow: hidden;
        border-radius: 5px;
    }
    
    .slide-preview {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        background-color: #2c3e50;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .slide-content {
        padding: 20px;
        text-align: center;
        color: white;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.6);
        background-color: rgba(0, 0, 0, 0.3);
        border-radius: 10px;
        max-width: 80%;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const imageGroup = document.getElementById('imageGroup');
    const colorGroup = document.getElementById('colorGroup');
    const imageInput = document.getElementById('image');
    const colorInput = document.getElementById('color');
    const previewSlide = document.getElementById('previewSlide');
    const previewTitle = document.getElementById('previewTitle');
    const previewDescription = document.getElementById('previewDescription');
    const titleInput = document.getElementById('title');
    const descriptionInput = document.getElementById('description');
    
    // Handle type change
    typeSelect.addEventListener('change', function() {
        if (this.value === 'image') {
            imageGroup.style.display = 'block';
            colorGroup.style.display = 'none';
            updatePreview();
        } else {
            imageGroup.style.display = 'none';
            colorGroup.style.display = 'block';
            updatePreview();
        }
    });
    
    // Update preview on image selection
    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewSlide.style.backgroundImage = `url('${e.target.result}')`;
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Update preview on color change
    colorInput.addEventListener('input', function() {
        previewSlide.style.backgroundColor = this.value;
        previewSlide.style.backgroundImage = 'none';
    });
    
    // Update preview text
    titleInput.addEventListener('input', function() {
        previewTitle.textContent = this.value || 'Título do Slide';
    });
    
    descriptionInput.addEventListener('input', function() {
        previewDescription.textContent = this.value || 'Descrição do slide irá aparecer aqui';
    });
    
    // Initial preview update
    function updatePreview() {
        if (typeSelect.value === 'image') {
            if (imageInput.files && imageInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewSlide.style.backgroundImage = `url('${e.target.result}')`;
                }
                reader.readAsDataURL(imageInput.files[0]);
            } else {
                previewSlide.style.backgroundImage = 'none';
                previewSlide.style.backgroundColor = '#2c3e50';
            }
        } else {
            previewSlide.style.backgroundImage = 'none';
            previewSlide.style.backgroundColor = colorInput.value;
        }
    }
});
</script>
