// Page-specific JavaScript functions for Engenha Rio

// Initialize page functions on load
document.addEventListener('DOMContentLoaded', function() {
    initializePageSpecificFunctions();
});

function initializePageSpecificFunctions() {
    // Add click handlers to all workflow buttons
    initializeWorkflowButtons();
    
    // Add click handlers to category filters
    initializeCategoryFilters();
    
    // Add search functionality
    initializePageSearch();
    
    // Add drag and drop file upload
    initializeDragDrop();
    
    // Add admin button handlers
    initializeAdminButtons();
}

// Workflow specific functions
function initializeWorkflowButtons() {
    // Add onclick to all delete buttons that don't have it
    document.querySelectorAll('button[title="Excluir"]:not([onclick])').forEach((btn, index) => {
        btn.setAttribute('onclick', `deleteDocument('doc-${index + 1}')`);
    });
    
    // Add onclick to all info buttons that don't have it
    document.querySelectorAll('button[title="Informações"]:not([onclick])').forEach((btn, index) => {
        btn.setAttribute('onclick', `viewDocument('doc-${index + 1}')`);
    });
    
    // Add onclick to all download buttons that don't have it
    document.querySelectorAll('button[title="Download"]:not([onclick])').forEach((btn, index) => {
        btn.setAttribute('onclick', `downloadDocument('doc-${index + 1}')`);
    });
}

// Category filter functionality
function initializeCategoryFilters() {
    const categoryButtons = document.querySelectorAll('.dropdown-item[data-category]');
    categoryButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const category = this.dataset.category;
            filterByCategory(category);
            
            // Update dropdown text
            const dropdownButton = document.querySelector('.dropdown-toggle');
            if (dropdownButton) {
                dropdownButton.innerHTML = `<i class="fas fa-filter me-2"></i>${this.textContent}`;
            }
        });
    });
}

// Search functionality for specific pages
function initializePageSearch() {
    const searchInput = document.querySelector('input[placeholder*="Buscar"]');
    if (searchInput && !searchInput.hasAttribute('onkeyup')) {
        searchInput.addEventListener('input', debounce(function(e) {
            const query = e.target.value.toLowerCase();
            filterTableRows(query);
        }, 300));
    }
}

function filterTableRows(query) {
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const shouldShow = query === '' || text.includes(query);
        row.style.display = shouldShow ? '' : 'none';
    });
}

// Drag and drop file upload
function initializeDragDrop() {
    const uploadAreas = document.querySelectorAll('.upload-area, .drop-zone');
    
    uploadAreas.forEach(area => {
        area.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });
        
        area.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
        });
        
        area.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileUpload(files[0], this);
            }
        });
        
        area.addEventListener('click', function() {
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.pdf,.doc,.docx,.dwg,.jpg,.jpeg,.png,.xls,.xlsx';
            fileInput.onchange = function(e) {
                if (e.target.files.length > 0) {
                    handleFileUpload(e.target.files[0], area);
                }
            };
            fileInput.click();
        });
    });
}

function handleFileUpload(file, area) {
    showLoader();
    
    const formData = new FormData();
    formData.append('document', file); // Changed from 'file' to 'document' to match server expectations
    formData.append('project_id', getProjectIdFromUrl());
    formData.append('stage', getCurrentStage());
    
    console.log('Uploading file:', file.name);
    console.log('Project ID:', getProjectIdFromUrl());
    console.log('Current stage:', getCurrentStage());
    
    fetch('/documents/upload-project-file', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            console.error('Server response not OK:', response.status, response.statusText);
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Upload response:', data);
        if (data.success) {
            showAlert('success', 'Arquivo enviado com sucesso!');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('error', data.message || 'Erro ao enviar arquivo');
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        showAlert('error', 'Erro de conexão: ' + error.message);
    })
    .finally(() => {
        hideLoader();
    });
}

function getProjectIdFromUrl() {
    const pathParts = window.location.pathname.split('/');
    return pathParts[pathParts.length - 1] || '1';
}

function getCurrentStage() {
    const activeStage = document.querySelector('.stage-icon.in-progress');
    if (activeStage) {
        const stageItem = activeStage.closest('.stage-item');
        const stageLabel = stageItem.querySelector('.stage-label');
        return stageLabel ? stageLabel.textContent.toLowerCase() : 'documentos';
    }
    return 'documentos';
}

// Stage navigation
function navigateToStage(stageName) {
    showLoader();
    
    const projectId = getProjectIdFromUrl();
    
    fetch(`/document-workflow/update-stage`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            project_id: projectId,
            stage: stageName 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', `Etapa alterada para: ${stageName}`);
            updateStageVisual(stageName);
        } else {
            showAlert('error', data.message || 'Erro ao alterar etapa');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Erro de conexão');
    })
    .finally(() => {
        hideLoader();
    });
}

function updateStageVisual(stageName) {
    // Update stage visual indicators
    document.querySelectorAll('.stage-icon').forEach(icon => {
        icon.classList.remove('in-progress');
        icon.classList.add('completed');
    });
    
    // Find and mark current stage
    const stageLabels = document.querySelectorAll('.stage-label');
    stageLabels.forEach(label => {
        if (label.textContent.toLowerCase() === stageName.toLowerCase()) {
            const icon = label.parentElement.querySelector('.stage-icon');
            if (icon) {
                icon.classList.remove('completed');
                icon.classList.add('in-progress');
            }
        }
    });
}

// Workflow actions
function approveStage(documentId) {
    if (confirm('Tem certeza que deseja aprovar esta etapa?')) {
        updateDocumentWorkflowStatus(documentId, 'approved');
    }
}

function rejectStage(documentId) {
    const reason = prompt('Motivo da rejeição (opcional):');
    updateDocumentWorkflowStatus(documentId, 'rejected', reason);
}

// Support and notification functions
function markNotificationAsRead(notificationId) {
    fetch(`/notifications/mark-read/${notificationId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationElement) {
                notificationElement.classList.add('read');
            }
        }
    })
    .catch(error => console.error('Notification error:', error));
}

function markAllNotificationsAsRead() {
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Todas as notificações foram marcadas como lidas');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        }
    })
    .catch(error => console.error('Notification error:', error));
}

// Support ticket functions
function updateTicketStatus(ticketId, status) {
    fetch(`/update-ticket-status.php?id=${ticketId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', `Status do ticket atualizado para: ${status.replace('_', ' ')}`);
            
            // Update UI to reflect the new status
            const statusBadges = document.querySelectorAll('.ticket-meta .badge');
            statusBadges.forEach(badge => {
                if (badge.classList.contains('bg-success') || 
                    badge.classList.contains('bg-warning') || 
                    badge.classList.contains('bg-secondary')) {
                    
                    // Remove existing status classes
                    badge.classList.remove('bg-success', 'bg-warning', 'bg-secondary');
                    
                    // Add new status class
                    if (status === 'aberto') {
                        badge.classList.add('bg-success');
                        badge.textContent = 'Aberto';
                    } else if (status === 'em_andamento') {
                        badge.classList.add('bg-warning');
                        badge.textContent = 'Em Andamento';
                    } else if (status === 'fechado') {
                        badge.classList.add('bg-secondary');
                        badge.textContent = 'Fechado';
                    }
                }
            });
            
            // Reload page after a short delay to reflect all changes
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('error', data.message || 'Erro ao atualizar status');
        }
    })
    .catch(error => {
        console.error('Support ticket error:', error);
        showAlert('error', 'Erro de conexão');
    });
}

// Export/Import functions
function exportData(type) {
    showLoader();
    
    const link = document.createElement('a');
    link.href = `/export/${type}`;
    link.download = `engenha-rio-${type}-${new Date().toISOString().split('T')[0]}.xlsx`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    hideLoader();
    showAlert('success', 'Exportação iniciada com sucesso!');
}

function importData() {
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = '.xlsx,.csv';
    fileInput.onchange = function(e) {
        if (e.target.files.length > 0) {
            uploadImportFile(e.target.files[0]);
        }
    };
    fileInput.click();
}

function uploadImportFile(file) {
    showLoader();
    
    const formData = new FormData();
    formData.append('import_file', file);
    
    fetch('/import/data', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Dados importados com sucesso!');
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showAlert('error', data.message || 'Erro na importação');
        }
    })
    .catch(error => {
        console.error('Import error:', error);
        showAlert('error', 'Erro de conexão');
    })
    .finally(() => {
        hideLoader();
    });
}

// Dashboard chart updates
function updateDashboardCharts() {
    showLoader();
    
    fetch('/dashboard/chart-data')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateProjectChart(data.projects);
                updateDocumentChart(data.documents);
                updateUserChart(data.users);
            }
        })
        .catch(error => console.error('Chart update error:', error))
        .finally(() => hideLoader());
}

function updateProjectChart(data) {
    // Update project status chart if exists
    const chartElement = document.getElementById('projectChart');
    if (chartElement && window.Chart) {
        // Chart.js implementation would go here
        console.log('Updating project chart with:', data);
    }
}

function updateDocumentChart(data) {
    // Update document workflow chart if exists
    const chartElement = document.getElementById('documentChart');
    if (chartElement && window.Chart) {
        // Chart.js implementation would go here
        console.log('Updating document chart with:', data);
    }
}

function updateUserChart(data) {
    // Update user activity chart if exists
    const chartElement = document.getElementById('userChart');
    if (chartElement && window.Chart) {
        // Chart.js implementation would go here
        console.log('Updating user chart with:', data);
    }
}

// Add CSS for drag and drop
const additionalStyles = `
    .drag-over {
        border-color: #007bff !important;
        background-color: #e3f2fd !important;
    }
    
    .upload-area {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .upload-area:hover {
        border-color: #007bff;
        background-color: #f8f9fa;
    }
    
    .notification.read {
        opacity: 0.6;
        background-color: #f8f9fa;
    }
    
    .stage-item {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .stage-item:hover .stage-icon {
        transform: scale(1.1);
    }
    
    .workflow-actions {
        position: sticky;
        top: 20px;
        z-index: 100;
    }
`;

// Initialize admin button handlers
function initializeAdminButtons() {
    // Approve user buttons
    document.querySelectorAll('.approve-user').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            
            if (confirm(`Tem certeza que deseja aprovar o usuário "${userName}"?`)) {
                approveUser(userId);
            }
        });
    });
    
    // Reject user buttons
    document.querySelectorAll('.reject-user').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            
            if (confirm(`Tem certeza que deseja rejeitar o usuário "${userName}"?`)) {
                rejectUser(userId);
            }
        });
    });
    
    // Delete user buttons (for existing users table)
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            
            if (confirm(`Tem certeza que deseja excluir o usuário "${userName}"? Esta ação não pode ser desfeita.`)) {
                deleteUser(userId);
            }
        });
    });
}

// Inject additional styles
const styleSheet = document.createElement('style');
styleSheet.textContent = additionalStyles;
document.head.appendChild(styleSheet);
