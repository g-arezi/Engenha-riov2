<?php
/**
 * Debug do sistema de workflow
 */

require_once __DIR__ . '/../autoload.php';

use App\Core\Auth;
use App\Core\Database;

// Verificar se é uma requisição de status
if (isset($_GET['action']) && $_GET['action'] === 'check_project') {
    header('Content-Type: application/json');
    
    $db = new Database();
    $project = $db->find('projects', 'proj_1753892536_9899');
    
    if ($project) {
        echo json_encode([
            'success' => true,
            'project' => [
                'id' => $project['id'],
                'title' => $project['title'],
                'workflow_stage' => $project['workflow_stage'],
                'status' => $project['status'],
                'updated_at' => $project['updated_at']
            ]
        ], JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
    }
    exit;
}

// Verificar último projeto criado
if (isset($_GET['action']) && $_GET['action'] === 'check_latest_project') {
    header('Content-Type: application/json');
    
    $db = new Database();
    $projects = $db->findAll('projects');
    
    if ($projects) {
        // Ordenar por data de criação (mais recente primeiro)
        uasort($projects, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        $latestProject = reset($projects);
        
        echo json_encode([
            'success' => true,
            'latest_project' => [
                'id' => $latestProject['id'],
                'name' => $latestProject['name'],
                'workflow_stage' => $latestProject['workflow_stage'],
                'workflow_stage_type' => gettype($latestProject['workflow_stage']),
                'status' => $latestProject['status'],
                'created_at' => $latestProject['created_at'],
                'updated_at' => $latestProject['updated_at']
            ]
        ], JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nenhum projeto encontrado']);
    }
    exit;
}

header('Content-Type: text/plain');

echo "=== DEBUG WORKFLOW ===\n\n";

// Inicializar
$auth = new Auth();
$db = new Database();

echo "1. VERIFICAÇÃO DE AUTENTICAÇÃO:\n";
echo "Usuário autenticado: " . ($auth->check() ? 'SIM' : 'NÃO') . "\n";

if ($auth->check()) {
    $user = $auth->user();
    echo "ID do usuário: " . $user['id'] . "\n";
    echo "Role do usuário: " . $user['role'] . "\n";
    echo "Permissão manage_workflow: " . ($auth->hasPermission('projects.manage_workflow') ? 'SIM' : 'NÃO') . "\n";
}

echo "\n2. INFORMAÇÕES DA SESSÃO:\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "Session Data: " . print_r($_SESSION, true) . "\n";

echo "\n3. STATUS DO PROJETO:\n";
$project = $db->find('projects', 'proj_1753892536_9899');
if ($project) {
    echo "ID: " . $project['id'] . "\n";
    echo "Título: " . $project['title'] . "\n";
    echo "Etapa atual: " . $project['workflow_stage'] . "\n";
    echo "Status: " . $project['status'] . "\n";
    echo "Última atualização: " . $project['updated_at'] . "\n";
} else {
    echo "Projeto não encontrado!\n";
}

echo "\n4. TESTE DE PERMISSÕES:\n";
$roles = ['administrador', 'coordenador', 'analista'];
foreach ($roles as $role) {
    // Simular um usuário com essa role para testar permissões
    $_SESSION['user_role'] = $role;
    echo "Role $role - manage_workflow: " . ($auth->hasPermission('projects.manage_workflow') ? 'SIM' : 'NÃO') . "\n";
}

echo "\n5. ARQUIVOS DE SESSÃO:\n";
$sessionPath = __DIR__ . '/../data/sessions/';
if (is_dir($sessionPath)) {
    $files = scandir($sessionPath);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "Arquivo: $file\n";
            $content = file_get_contents($sessionPath . $file);
            echo "Conteúdo: $content\n\n";
        }
    }
} else {
    echo "Diretório de sessões não encontrado!\n";
}
?>
