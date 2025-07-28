// Engenha Rio - Advanced Functions
// Todas as funcionalidades para botões do sistema

// ===============================
// DOCUMENTO FUNCTIONS
// ===============================

function viewDocument(documentId) {
    showLoader();
    
    fetch(`/documents/view/${documentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = createDocumentModal(data.document);
                document.body.appendChild(modal);
                const bootstrapModal = new bootstrap.Modal(modal);
                bootstrapModal.show();
                
                modal.addEventListener('hidden.bs.modal', function () {
                    modal.remove();
                });
            } else {
                showAlert('error', data.message || 'Erro ao carregar documento');
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

function downloadDocument(documentId) {
    showLoader();
    
    // Verificar se o documento existe primeiro
    fetch(`/documents/view/${documentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Criar link temporário para download
                const link = document.createElement('a');
                link.href = `/documents/download/${documentId}`;
                link.download = data.document.original_name || '';
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Mostrar feedback de sucesso
                showAlert('success', `Download iniciado: ${data.document.name}`);
                
                // Rastrear download
                fetch(`/documents/track-download/${documentId}`, { method: 'POST' })
                    .catch(error => console.error('Download tracking error:', error));
            } else {
                showAlert('error', 'Documento não encontrado ou sem permissão');
            }
        })
        .catch(error => {
            console.error('Download error:', error);
            showAlert('error', 'Erro ao baixar documento');
        })
        .finally(() => {
            hideLoader();
        });
    
    hideLoader();
    showAlert('success', 'Download iniciado com sucesso!');
}

function editDocument(documentId) {
    window.location.href = `/documents/edit/${documentId}`;
}

function deleteDocument(documentId) {
    if (confirm('Tem certeza que deseja excluir este documento? Esta ação não pode ser desfeita.')) {
        showLoader();
        
        fetch(`/documents/delete/${documentId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Documento excluído com sucesso!');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('error', data.message || 'Erro ao excluir documento');
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
}

function createDocumentModal(document) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-alt me-2"></i>
                        ${document.name}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informações do Documento</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Nome:</strong></td>
                                    <td>${document.name}</td>
                                </tr>
                                <tr>
                                    <td><strong>Categoria:</strong></td>
                                    <td><span class="badge bg-primary">${document.category}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Tamanho:</strong></td>
                                    <td>${formatFileSize(document.size || 0)}</td>
                                </tr>
                                <tr>
                                    <td><strong>Data de envio:</strong></td>
                                    <td>${formatDate(document.created_at)}</td>
                                </tr>
                                <tr>
                                    <td><strong>Enviado por:</strong></td>
                                    <td>${document.uploaded_by || 'Sistema'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Descrição</h6>
                            <p class="text-muted">${document.description || 'Nenhuma descrição disponível'}</p>
                            
                            <h6>Ações</h6>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" onclick="downloadDocument('${document.id}')">
                                    <i class="fas fa-download me-2"></i>Download
                                </button>
                                <button class="btn btn-warning" onclick="editDocument('${document.id}')">
                                    <i class="fas fa-edit me-2"></i>Editar
                                </button>
                                <button class="btn btn-danger" onclick="deleteDocument('${document.id}')">
                                    <i class="fas fa-trash me-2"></i>Excluir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    return modal;
}

// ===============================
// PROJECT FUNCTIONS
// ===============================

function viewProject(projectId) {
    window.location.href = `/projects/${projectId}`;
}

function editProject(projectId) {
    window.location.href = `/projects/${projectId}/edit`;
}

function deleteProject(projectId) {
    if (confirm('Tem certeza que deseja excluir este projeto? Esta ação não pode ser desfeita.')) {
        showLoader();
        
        fetch(`/projects/${projectId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Projeto excluído com sucesso!');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('error', data.message || 'Erro ao excluir projeto');
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
}

function updateProjectStatus(projectId, newStatus) {
    showLoader();
    
    fetch(`/projects/update-status/${projectId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Status do projeto atualizado!');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('error', data.message || 'Erro ao atualizar status');
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

// ===============================
// WORKFLOW FUNCTIONS
// ===============================

function approveDocument(documentId) {
    if (confirm('Tem certeza que deseja aprovar este documento?')) {
        updateDocumentWorkflowStatus(documentId, 'approved');
    }
}

function rejectDocument(documentId) {
    const reason = prompt('Motivo da rejeição (opcional):');
    updateDocumentWorkflowStatus(documentId, 'rejected', reason);
}

function updateDocumentWorkflowStatus(documentId, status, reason = null) {
    showLoader();
    
    fetch(`/document-workflow/update-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            document_id: documentId,
            status: status,
            reason: reason 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', `Documento ${status === 'approved' ? 'aprovado' : 'rejeitado'} com sucesso!`);
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('error', data.message || 'Erro ao atualizar status');
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

function moveToNextStage(documentId) {
    if (confirm('Mover documento para a próxima etapa do workflow?')) {
        showLoader();
        
        fetch(`/document-workflow/advance`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                document_id: documentId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Documento movido para próxima etapa!');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('error', data.message || 'Erro ao mover documento');
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
}

// ===============================
// NOTIFICATION FUNCTIONS
// ===============================

function markNotificationAsRead(notificationId, link) {
    fetch(`/notifications/mark-read/${notificationId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirecionar para o link da notificação se existir
            if (link && link !== '#') {
                window.location.href = link;
            } else {
                // Recarregar a página para atualizar o contador
                window.location.reload();
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// ===============================
// ADMIN FUNCTIONS
// ===============================

function approveUser(userId) {
    showLoader();
    
    fetch(`/admin/users/${userId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Usuário aprovado com sucesso!');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('error', data.message || 'Erro ao aprovar usuário');
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

function rejectUser(userId) {
    showLoader();
    
    fetch(`/admin/users/${userId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Usuário rejeitado com sucesso!');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('error', data.message || 'Erro ao rejeitar usuário');
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

function deleteUser(userId) {
    if (confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.')) {
        showLoader();
        
        fetch(`/admin/delete-user/${userId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Usuário excluído com sucesso!');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('error', data.message || 'Erro ao excluir usuário');
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
}

function editUser(userId) {
    window.location.href = `/admin/edit-user/${userId}`;
}

function updateUserStatus(userId, status) {
    showLoader();
    
    fetch(`/admin/update-user-status/${userId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', `Usuário ${status === 'approved' ? 'aprovado' : 'rejeitado'} com sucesso!`);
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('error', data.message || 'Erro ao atualizar status');
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

// ===============================
// SUPPORT FUNCTIONS
// ===============================

function viewTicket(ticketId) {
    window.location.href = `/support/view/${ticketId}`;
}

function updateTicketStatus(ticketId, newStatus) {
    showLoader();
    
    fetch(`/support/update-status/${ticketId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Status do ticket atualizado!');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('error', data.message || 'Erro ao atualizar status');
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

function closeTicket(ticketId) {
    if (confirm('Tem certeza que deseja fechar este ticket?')) {
        updateTicketStatus(ticketId, 'fechado');
    }
}

// ===============================
// CATEGORY MANAGEMENT
// ===============================

function manageCategoriesModal() {
    fetch('/categories/list')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showCategoriesModal(data.categories);
            } else {
                showAlert('error', 'Erro ao carregar categorias');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Erro de conexão');
        });
}

function showCategoriesModal(categories) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-tags me-2"></i>
                        Gerenciar Categorias
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="text" class="form-control" id="newCategoryName" 
                                   placeholder="Nome da nova categoria">
                            <button class="btn btn-primary" onclick="addCategory()">
                                <i class="fas fa-plus"></i> Adicionar
                            </button>
                        </div>
                    </div>
                    
                    <div class="list-group" id="categoriesList">
                        ${categories.map(cat => `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>${cat.name}</span>
                                <div>
                                    <button class="btn btn-sm btn-outline-warning" 
                                            onclick="editCategory('${cat.id}', '${cat.name}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteCategory('${cat.id}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
    
    modal.addEventListener('hidden.bs.modal', function () {
        modal.remove();
    });
}

function addCategory() {
    const name = document.getElementById('newCategoryName').value.trim();
    if (!name) {
        showAlert('warning', 'Digite o nome da categoria');
        return;
    }
    
    fetch('/categories/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ name: name })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Categoria adicionada com sucesso!');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('error', data.message || 'Erro ao adicionar categoria');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Erro de conexão');
    });
}

function editCategory(categoryId, currentName) {
    const newName = prompt('Novo nome da categoria:', currentName);
    if (newName && newName.trim() !== currentName) {
        fetch(`/categories/update/${categoryId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ name: newName.trim() })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Categoria atualizada com sucesso!');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('error', data.message || 'Erro ao atualizar categoria');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Erro de conexão');
        });
    }
}

function deleteCategory(categoryId) {
    if (confirm('Tem certeza que deseja excluir esta categoria?')) {
        fetch(`/categories/delete/${categoryId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Categoria excluída com sucesso!');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('error', data.message || 'Erro ao excluir categoria');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Erro de conexão');
        });
    }
}

// ===============================
// SEARCH AND FILTER FUNCTIONS
// ===============================

function performAdvancedSearch(query, type) {
    if (query.length < 2) {
        clearSearchResults();
        return;
    }
    
    showLoader();
    
    fetch(`/search?q=${encodeURIComponent(query)}&type=${type}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySearchResults(data.results, type);
            }
        })
        .catch(error => {
            console.error('Search error:', error);
        })
        .finally(() => {
            hideLoader();
        });
}

function displaySearchResults(results, type) {
    console.log('Search results:', results);
    // Implementation depends on page context
}

function clearSearchResults() {
    const resultsContainer = document.getElementById('searchResults');
    if (resultsContainer) {
        resultsContainer.innerHTML = '';
    }
}

function filterByCategory(category) {
    const items = document.querySelectorAll('[data-category]');
    
    items.forEach(item => {
        if (category === 'all' || item.dataset.category === category) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
    
    // Update active filter button
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-filter="${category}"]`)?.classList.add('active');
}

// ===============================
// UTILITY FUNCTIONS
// ===============================

function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert-custom');
    existingAlerts.forEach(alert => alert.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-custom`;
    alertDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    
    const icon = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };
    
    alertDiv.innerHTML = `
        <i class="${icon[type]} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

function showLoader() {
    let loader = document.getElementById('globalLoader');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'globalLoader';
        loader.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 99999;
        `;
        loader.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        `;
        document.body.appendChild(loader);
    }
    loader.style.display = 'flex';
}

function hideLoader() {
    const loader = document.getElementById('globalLoader');
    if (loader) {
        loader.style.display = 'none';
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function formatDate(dateString) {
    if (!dateString) return 'Data não disponível';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
