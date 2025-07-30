/**
 * Gerenciamento do workflow por etapas
 * 
 * Este script gerencia o workflow por etapas para projetos do Engenha Rio:
 * - Documentos
 * - Projeto
 * - Produção
 * - Buildup
 * - Aprovado
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeWorkflowStages();
    colorizeDocumentsByStatus();
});

/**
 * Inicializa os estágios do workflow na interface
 */
function initializeWorkflowStages() {
    // Obter o estágio atual do projeto
    const currentStage = document.getElementById('workflow-stage') ? 
                        document.getElementById('workflow-stage').value : '1';
    
    // Atualizar a aparência visual dos estágios
    updateStagesVisual(currentStage);
    
    // Adicionar event listeners para cliques nos estágios
    document.querySelectorAll('.stage-icon').forEach(icon => {
        icon.addEventListener('click', function() {
            const stageElement = this.closest('.stage-item');
            const stageLabel = stageElement.querySelector('.stage-label').textContent;
            
            // Se o estágio estiver desativado, não fazer nada
            if (this.classList.contains('disabled')) {
                return;
            }
            
            // Exibir informações do estágio
            showStageInfo(stageLabel);
        });
    });
}

/**
 * Atualiza a aparência visual dos estágios do workflow
 * @param {string} currentStage - O estágio atual (1-5)
 */
function updateStagesVisual(currentStage) {
    const stages = document.querySelectorAll('.stage-item');
    
    // Converter para número se for string
    currentStage = parseInt(currentStage);
    
    stages.forEach((stage, index) => {
        const icon = stage.querySelector('.stage-icon');
        const stageNumber = index + 1;
        
        // Remover classes existentes
        icon.classList.remove('current', 'completed', 'disabled', 'analysis');
        
        if (stageNumber < currentStage) {
            // Estágios anteriores: completados
            icon.classList.add('completed');
            icon.innerHTML = '<i class="fas fa-check"></i>';
        } else if (stageNumber === currentStage) {
            // Estágio atual: em andamento
            icon.classList.add('current');
            
            // O primeiro estágio (Documentos) usa um ícone de documento
            if (stageNumber === 1) {
                icon.innerHTML = '<i class="fas fa-file-alt"></i>';
            } else {
                icon.innerHTML = '<i class="fas fa-sync-alt"></i>';
            }
        } else {
            // Estágios futuros: desativados
            icon.classList.add('disabled');
            icon.innerHTML = '<i class="fas fa-lock"></i>';
        }
    });
}

/**
 * Exibe informações detalhadas sobre o estágio selecionado
 * @param {string} stageName - Nome do estágio
 */
function showStageInfo(stageName) {
    // Mapear os nomes dos estágios para informações
    const stageInfo = {
        'Documentos': {
            title: 'Etapa de Documentos',
            description: 'Nesta etapa, todos os documentos necessários para o projeto devem ser enviados e aprovados.'
        },
        'Projeto': {
            title: 'Etapa de Projeto',
            description: 'Nesta etapa, o projeto está sendo elaborado e revisado pelos analistas.'
        },
        'Produção': {
            title: 'Etapa de Produção',
            description: 'Nesta etapa, o projeto está sendo produzido/executado.'
        },
        'Buildup': {
            title: 'Etapa de Buildup',
            description: 'Nesta etapa, o projeto está em fase de montagem/construção.'
        },
        'Aprovado': {
            title: 'Etapa de Aprovação Final',
            description: 'Nesta etapa, o projeto foi finalizado e aprovado.'
        }
    };
    
    // Obter informações do estágio atual
    const info = stageInfo[stageName] || {
        title: stageName,
        description: 'Informações não disponíveis para este estágio.'
    };
    
    // Criar e exibir um modal com as informações
    const modalHtml = `
        <div class="modal fade" id="stageInfoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${info.title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>${info.description}</p>
                        
                        <div class="stage-documents mt-3">
                            <h6>Requisitos para esta etapa:</h6>
                            <ul class="stage-requirements">
                                ${getStageRequirements(stageName)}
                            </ul>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Para avançar para a próxima etapa, todos os requisitos devem estar concluídos.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Adicionar o modal ao documento
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHtml;
    document.body.appendChild(modalContainer);
    
    // Exibir o modal
    const modal = new bootstrap.Modal(document.getElementById('stageInfoModal'));
    modal.show();
    
    // Remover o modal do DOM quando for fechado
    document.getElementById('stageInfoModal').addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modalContainer);
    });
}

/**
 * Retorna os requisitos HTML para um determinado estágio
 * @param {string} stageName - Nome do estágio
 * @returns {string} HTML dos requisitos
 */
function getStageRequirements(stageName) {
    // Mapear os requisitos para cada estágio
    const requirements = {
        'Documentos': [
            'Todos os documentos obrigatórios enviados',
            'Documentos aprovados pelo analista'
        ],
        'Projeto': [
            'Análise técnica realizada',
            'Projeto elaborado',
            'Projeto revisado e aprovado'
        ],
        'Produção': [
            'Material adquirido',
            'Produção iniciada',
            'Testes de qualidade'
        ],
        'Buildup': [
            'Montagem iniciada',
            'Instalação de equipamentos',
            'Verificações de segurança'
        ],
        'Aprovado': [
            'Verificação final',
            'Aprovação do cliente',
            'Documentação de entrega'
        ]
    };
    
    const stageReqs = requirements[stageName] || ['Requisitos não definidos'];
    
    return stageReqs.map(req => `<li>${req}</li>`).join('');
}

/**
 * Coloriza as linhas da tabela de documentos de acordo com o status
 */
function colorizeDocumentsByStatus() {
    const documentRows = document.querySelectorAll('tbody tr');
    
    documentRows.forEach(row => {
        // Verificar o status pelo texto ou classe da badge
        const statusBadge = row.querySelector('.badge') || row.querySelector('.status-select');
        
        if (!statusBadge) return;
        
        let status;
        
        // Se for um select de status
        if (statusBadge.tagName === 'SELECT') {
            status = statusBadge.value;
        } 
        // Se for uma badge
        else {
            const badgeText = statusBadge.textContent.trim().toLowerCase();
            if (badgeText.includes('aprovado')) status = 'aprovado';
            else if (badgeText.includes('rejeitado')) status = 'rejeitado';
            else if (badgeText.includes('análise')) status = 'em_analise';
            else status = 'pendente';
        }
        
        // Adicionar classe baseada no status
        if (status === 'rejeitado' || status === 'em_analise') {
            row.classList.add('document-row', 'status-rejected');
        } else if (status === 'aprovado') {
            row.classList.add('document-row', 'status-approved');
        }
    });
}
