<?php
// Script para migração dos projetos com workflow_stage
require_once 'autoload.php';

use App\Core\Database;

$db = new Database();

// Ler projetos existentes
$projects = $db->findAll('projects');

$updated = 0;
// Atualizar cada projeto com workflow_stage se não tiver
foreach ($projects as $project) {
    $needsUpdate = false;
    $updateData = [];
    
    if (!isset($project['workflow_stage'])) {
        $updateData['workflow_stage'] = 1; // Início na etapa "Documentos"
        $needsUpdate = true;
        echo "Projeto '{$project['name']}' será atualizado com workflow_stage = 1\n";
    }
    
    // Verificar outros campos necessários
    if (!isset($project['start_date'])) {
        $updateData['start_date'] = $project['created_at'] ?? date('Y-m-d');
        $needsUpdate = true;
    }
    
    if (!isset($project['updated_at'])) {
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        $needsUpdate = true;
    }
    
    if ($needsUpdate) {
        $db->update('projects', $project['id'], $updateData);
        $updated++;
    }
}

echo "Migração concluída! Total de projetos atualizados: $updated\n";
