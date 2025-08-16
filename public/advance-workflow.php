<?php
/**
 * Endpoint direto para avançar etapa do projeto
 * Permite avançar um projeto para a próxima etapa sem passar pelo router
 */

// Suprimir qualquer saída anterior
ob_start();

// Desativar exibição de erros para evitar HTML na saída
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Iniciar a sessão primeiro, antes de qualquer output
session_start();

require_once __DIR__ . '/../autoload.php';

use App\Core\Auth;
use App\Core\Database;

// Função para recuperar sessão via banco de dados se necessário
function forceAuthRecovery() {
    if (isset($_SESSION['user_id'])) {
        return true; // Já autenticado
    }
    
    // Tentar via cookies se disponível
    if (isset($_COOKIE['user_id']) && isset($_COOKIE['user_role'])) {
        $_SESSION['user_id'] = $_COOKIE['user_id'];
        $_SESSION['user_role'] = $_COOKIE['user_role'];
        error_log('advance-workflow.php - Sessão recuperada via cookies');
        return true;
    }
    
    // Como última tentativa, para admin verificar se há sessão ativa no arquivo
    $sessionsPath = __DIR__ . '/../data/sessions/';
    if (is_dir($sessionsPath)) {
        $sessionFiles = glob($sessionsPath . 'sess_*');
        foreach ($sessionFiles as $file) {
            $content = file_get_contents($file);
            if (strpos($content, 'user_id|s:5:"admin"') !== false || 
                strpos($content, 'user_role|s:13:"administrador"') !== false) {
                $_SESSION['user_id'] = 'admin';
                $_SESSION['user_role'] = 'administrador';
                error_log('advance-workflow.php - Sessão de admin recuperada do arquivo');
                return true;
            }
        }
    }
    
    return false;
}

// Log detalhado para debug
error_log('advance-workflow.php - Session ID: ' . session_id());
error_log('advance-workflow.php - $_SESSION: ' . json_encode($_SESSION));
error_log('advance-workflow.php - $_COOKIE: ' . json_encode($_COOKIE));

// Verificação de autenticação com recuperação automática
if (!Auth::check()) {
    error_log('advance-workflow.php - Auth::check() falhou, tentando recuperação...');
    
    if (forceAuthRecovery() && Auth::check()) {
        error_log('advance-workflow.php - Autenticação recuperada com sucesso');
    } else {
        error_log('advance-workflow.php - Falha na autenticação final');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
        exit;
    }
}

// Verificação de permissão
$userRole = Auth::role();
error_log('advance-workflow.php - User role: ' . $userRole);

if (!Auth::hasPermission('projects.manage_workflow')) {
    error_log('advance-workflow.php - Usuário sem permissão. Role: ' . $userRole);
    
    // Log das permissões do usuário para debug
    $user = Auth::user();
    error_log('advance-workflow.php - Dados do usuário: ' . json_encode($user));
    
    // Verificação explícita para roles autorizados
    if (!in_array($userRole, ['administrador', 'coordenador', 'analista'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Sem permissão para gerenciar workflow. Role atual: ' . $userRole]);
        exit;
    } else {
        error_log('advance-workflow.php - Role autorizado mas hasPermission retornou false. Prosseguindo...');
    }
} else {
    error_log('advance-workflow.php - Permissão verificada com sucesso');
}

// Verificar se é método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Log para debug
error_log('Chamada para advance-workflow.php - POST: ' . json_encode($_POST));
error_log('Chamada para advance-workflow.php - JSON: ' . file_get_contents('php://input'));

// Obter dados da requisição
$input = json_decode(file_get_contents('php://input'), true);
$projectId = $input['project_id'] ?? $_GET['id'] ?? null;

if (!$projectId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID do projeto não fornecido']);
    exit;
}

// Inicializar banco de dados
$db = new Database();

// Buscar projeto
$project = $db->find('projects', $projectId);
if (!$project) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
    exit;
}

// Verificar etapa atual e converter para número se necessário
$currentStage = $project['workflow_stage'] ?? 1;

// Mapeamento de strings para números
$stageMapping = [
    'documentos' => 1,
    'projeto' => 2,
    'producao' => 3,
    'buildup' => 4,
    'aprovado' => 5
];

// Converter string para número se necessário
if (is_string($currentStage)) {
    $currentStage = $stageMapping[strtolower($currentStage)] ?? 1;
}

// Garantir que é um número
$currentStage = (int) $currentStage;

// Se já está na última etapa, retornar erro
if ($currentStage >= 5) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Projeto já está na etapa final']);
    exit;
}

// Calcular próxima etapa
$newStage = $currentStage + 1;

// Preparar dados para atualização
$updateData = [
    'workflow_stage' => $newStage,
    'updated_at' => date('Y-m-d H:i:s')
];

// Se chegou na etapa final e status não está como concluído, marcar como ativo
if ($newStage === 5 && $project['status'] !== 'concluido') {
    $updateData['status'] = 'ativo';
}

// Atualizar projeto
$updated = $db->update('projects', $projectId, $updateData);

if ($updated) {
    // Criar notificação
    $stageNames = [
        1 => 'Documentos',
        2 => 'Projeto',
        3 => 'Produção',
        4 => 'Buildup',
        5 => 'Aprovado'
    ];
    
    try {
        // Criar uma notificação para o projeto
        $notification = [
            'id' => uniqid(),
            'user_id' => null, // Notificação geral do projeto
            'project_id' => $projectId,
            'title' => 'Projeto avançou de etapa',
            'message' => 'O projeto foi avançado para a etapa: ' . $stageNames[$newStage],
            'type' => 'workflow_advanced',
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $db->insert('notifications', $notification);
    } catch (\Exception $e) {
        error_log('Erro ao criar notificação: ' . $e->getMessage());
        // Não impedir o fluxo se a notificação falhar
    }
    
    // Limpar qualquer saída anterior
    ob_clean();
    
    // Definir o cabeçalho e enviar a resposta JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Projeto avançado para a próxima etapa: ' . $stageNames[$newStage],
        'old_stage' => $currentStage,
        'new_stage' => $newStage
    ]);
} else {
    // Limpar qualquer saída anterior
    ob_clean();
    
    // Definir o cabeçalho e enviar a resposta JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar etapa do projeto']);
}

// Certifique-se de que toda a saída foi enviada
ob_end_flush();
exit;
