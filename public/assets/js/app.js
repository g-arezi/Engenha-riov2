// Engenha Rio - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initializeSidebar();
    initializeTooltips();
    initializeDropdowns();
    initializeModals();
    initializeTabs();
    initializeSearch();
    initializeFileUpload();
    initializeFormValidation();
    
    // Add fade-in animation to main content
    document.querySelector('.main-content')?.classList.add('fade-in');
});

// Sidebar functionality
function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    }
}

// Initialize Bootstrap tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialize Bootstrap dropdowns
function initializeDropdowns() {
    const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    dropdownElementList.map(function(dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
}

// Initialize Bootstrap modals
function initializeModals() {
    const modalElements = document.querySelectorAll('.modal');
    modalElements.forEach(modal => {
        new bootstrap.Modal(modal);
    });
}

// Tab functionality
function initializeTabs() {
    const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const target = this.getAttribute('data-bs-target');
            showTab(target);
        });
    });
    
    // Support tabs specific functionality
    const supportTabs = document.querySelectorAll('.support-tab');
    supportTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            supportTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const tabType = this.getAttribute('data-tab');
            loadSupportContent(tabType);
        });
    });
}

function showTab(targetId) {
    // Hide all tab panes
    const tabPanes = document.querySelectorAll('.tab-pane');
    tabPanes.forEach(pane => {
        pane.classList.remove('show', 'active');
    });
    
    // Show target tab pane
    const targetPane = document.querySelector(targetId);
    if (targetPane) {
        targetPane.classList.add('show', 'active');
    }
}

// Search functionality
function initializeSearch() {
    const searchInputs = document.querySelectorAll('input[type="search"], input[placeholder*="Buscar"]');
    
    searchInputs.forEach(input => {
        let debounceTimer;
        
        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                performSearch(this.value, this);
            }, 300);
        });
    });
}

function performSearch(query, inputElement) {
    const targetTable = inputElement.closest('.card')?.querySelector('table tbody');
    
    if (targetTable && query.length > 0) {
        const rows = targetTable.querySelectorAll('tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const match = text.includes(query.toLowerCase());
            row.style.display = match ? '' : 'none';
        });
    } else if (targetTable) {
        // Show all rows if search is empty
        const rows = targetTable.querySelectorAll('tr');
        rows.forEach(row => {
            row.style.display = '';
        });
    }
}

// File upload functionality
function initializeFileUpload() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            handleFileSelect(this);
        });
        
        // Drag and drop support
        const dropZone = input.closest('.drop-zone');
        if (dropZone) {
            initializeDropZone(dropZone, input);
        }
    });
}

function handleFileSelect(input) {
    const files = input.files;
    const preview = input.closest('.form-group')?.querySelector('.file-preview');
    
    if (preview && files.length > 0) {
        updateFilePreview(preview, files[0]);
    }
    
    // Validate file
    if (files.length > 0) {
        validateFile(files[0], input);
    }
}

function updateFilePreview(preview, file) {
    const fileSize = formatFileSize(file.size);
    const fileName = file.name;
    const fileExtension = file.name.split('.').pop().toLowerCase();
    
    // Escolher ícone baseado no tipo de arquivo
    let iconClass = 'fas fa-file';
    let iconColor = 'text-primary';
    
    switch (fileExtension) {
        case 'pdf':
            iconClass = 'fas fa-file-pdf';
            iconColor = 'text-danger';
            break;
        case 'doc':
        case 'docx':
            iconClass = 'fas fa-file-word';
            iconColor = 'text-primary';
            break;
        case 'xls':
        case 'xlsx':
            iconClass = 'fas fa-file-excel';
            iconColor = 'text-success';
            break;
        case 'jpg':
        case 'jpeg':
        case 'png':
            iconClass = 'fas fa-file-image';
            iconColor = 'text-info';
            break;
    }
    
    preview.innerHTML = `
        <div class="file-info bg-light border rounded p-3 d-flex align-items-center">
            <div class="file-icon me-3">
                <i class="${iconClass} fa-3x ${iconColor}"></i>
            </div>
            <div class="file-details flex-grow-1">
                <div class="file-name fw-bold text-dark">${fileName}</div>
                <div class="file-size text-muted">${fileSize}</div>
                <div class="file-type text-muted">${fileExtension.toUpperCase()}</div>
                <div class="file-status text-success mt-1">
                    <i class="fas fa-check-circle me-1"></i>
                    Arquivo selecionado com sucesso
                </div>
            </div>
            <div class="file-actions">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearFileSelection(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    preview.style.display = 'block';
}

function clearFileSelection(button) {
    const preview = button.closest('.file-preview');
    const input = preview.parentElement.querySelector('input[type="file"]');
    
    input.value = '';
    preview.style.display = 'none';
    preview.innerHTML = '';
}

function validateFile(file, input) {
    const maxSize = 40 * 1024 * 1024; // 40MB (conforme configuração PHP)
    const allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xls', 'xlsx', 'txt'];
    
    const fileExtension = file.name.split('.').pop().toLowerCase();
    
    if (file.size > maxSize) {
        showAlert('error', 'Arquivo muito grande. Tamanho máximo: 40MB');
        clearFileInput(input);
        return false;
    }
    
    if (!allowedTypes.includes(fileExtension)) {
        showAlert('error', `Tipo de arquivo "${fileExtension}" não permitido. Tipos aceitos: ${allowedTypes.join(', ').toUpperCase()}`);
        clearFileInput(input);
        return false;
    }
    
    return true;
}

function clearFileInput(input) {
    input.value = '';
    const preview = input.closest('.form-group')?.querySelector('.file-preview');
    if (preview) {
        preview.style.display = 'none';
        preview.innerHTML = '';
    }
}

function initializeDropZone(dropZone, input) {
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('drag-over');
    });
    
    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
    });
    
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            input.files = files;
            handleFileSelect(input);
        }
    });
}

// Form validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[data-validate="true"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            this.classList.add('was-validated');
        });
    });
}

function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Utility functions
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alertContainer') || createAlertContainer();
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alertContainer';
    container.className = 'position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// Support page specific functions
function loadSupportContent(tabType) {
    const contentArea = document.querySelector('.support-content');
    if (!contentArea) return;
    
    if (tabType === 'open') {
        // Load open tickets
        console.log('Loading open tickets...');
    } else if (tabType === 'history') {
        // Load ticket history
        console.log('Loading ticket history...');
    }
}

// Project management functions
function updateProjectStatus(projectId, status) {
    fetch(`/api/projects/${projectId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Status do projeto atualizado com sucesso', 'success');
            // Refresh the page or update the UI
            location.reload();
        } else {
            showAlert('Erro ao atualizar status do projeto', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erro de conexão', 'danger');
    });
}

// Document management functions
function confirmDelete(message = 'Tem certeza que deseja excluir este item?') {
    return confirm(message);
}

// Theme management
function toggleTheme() {
    const body = document.body;
    const currentTheme = body.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    body.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
}

// Load saved theme
function loadTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.setAttribute('data-theme', savedTheme);
}

// Initialize theme on page load
loadTheme();

// Document workflow functions
function approveDocument(documentId) {
    if (confirm('Tem certeza que deseja aprovar este documento?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/documents/${documentId}/approve`;
        
        const comments = prompt('Comentários (opcional):');
        if (comments !== null) {
            const commentsInput = document.createElement('input');
            commentsInput.type = 'hidden';
            commentsInput.name = 'comments';
            commentsInput.value = comments;
            form.appendChild(commentsInput);
        }
        
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectDocument(documentId) {
    const reason = prompt('Motivo da rejeição:');
    if (reason && reason.trim()) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/documents/${documentId}/reject`;
        
        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'rejection_reason';
        reasonInput.value = reason;
        form.appendChild(reasonInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function downloadProjectDocument(documentId) {
    window.location.href = `/documents/project/${documentId}/download`;
}

function openUploadModal(projectId, documentType) {
    window.location.href = `/documents/project/upload?project_id=${projectId}&document_type=${documentType}`;
}

// Stage management functions
function updateStageStatus(stageElement, status) {
    const icon = stageElement.querySelector('.stage-icon');
    icon.className = `stage-icon ${status}`;
    
    switch(status) {
        case 'completed':
            icon.innerHTML = '<i class="fas fa-check"></i>';
            icon.style.backgroundColor = '#28a745';
            break;
        case 'in_progress':
            icon.innerHTML = '<i class="fas fa-clock"></i>';
            icon.style.backgroundColor = '#ffc107';
            break;
        case 'disabled':
            icon.innerHTML = '<i class="fas fa-lock"></i>';
            icon.style.backgroundColor = '#6c757d';
            break;
    }
}

function calculateProjectProgress(documents) {
    const totalDocuments = documents.length;
    const approvedDocuments = documents.filter(doc => doc.status === 'aprovado').length;
    
    return totalDocuments > 0 ? Math.round((approvedDocuments / totalDocuments) * 100) : 0;
}

// Export functions for global use
window.EngenhaRio = {
    ...window.EngenhaRio,
    approveDocument,
    rejectDocument,
    downloadProjectDocument,
    openUploadModal,
    updateStageStatus,
    calculateProjectProgress
};
