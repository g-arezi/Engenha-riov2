<?php
// Script para inicializar dados de exemplo

$dataPath = __DIR__ . '/data/';

// Verificar se o arquivo projects.json existe e tem conteúdo
$projectsFile = $dataPath . 'projects.json';
if (!file_exists($projectsFile) || trim(file_get_contents($projectsFile)) === '{}') {
    $projects = [
        "projeto_1" => [
            "id" => "projeto_1",
            "name" => "Reforma da Ponte do Centro",
            "description" => "Projeto de reforma estrutural da ponte principal do centro da cidade",
            "client_id" => "rafael",
            "client_name" => "Prefeitura Municipal",
            "analyst_id" => "rafael",
            "analyst_name" => "Rafael Edinaldo", 
            "status" => "ativo",
            "priority" => "alta",
            "deadline" => "2025-12-31",
            "created_by" => "admin",
            "created_at" => "2025-07-20 10:00:00",
            "updated_at" => "2025-07-20 10:00:00"
        ]
    ];
    
    file_put_contents($projectsFile, json_encode($projects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "✅ Dados de projetos de exemplo criados\n";
}

// Verificar se o arquivo notifications.json existe e tem conteúdo  
$notificationsFile = $dataPath . 'notifications.json';
if (!file_exists($notificationsFile) || trim(file_get_contents($notificationsFile)) === '{}') {
    $notifications = [
        "notif_1" => [
            "id" => "notif_1",
            "user_id" => "admin",
            "type" => "document_uploaded",
            "title" => "Novo documento enviado",
            "message" => "Um novo documento foi enviado para o projeto Reforma da Ponte do Centro",
            "data" => [
                "project_id" => "projeto_1",
                "project_name" => "Reforma da Ponte do Centro",
                "document_id" => "doc_1",
                "document_name" => "Planta Baixa.pdf"
            ],
            "read" => false,
            "read_at" => null,
            "created_at" => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ],
        "notif_2" => [
            "id" => "notif_2", 
            "user_id" => "admin",
            "type" => "document_approved",
            "title" => "Documento aprovado",
            "message" => "O documento Especificação Técnica.pdf foi aprovado no projeto Reforma da Ponte do Centro",
            "data" => [
                "project_id" => "projeto_1",
                "project_name" => "Reforma da Ponte do Centro",
                "document_id" => "doc_2",
                "document_name" => "Especificação Técnica.pdf"
            ],
            "read" => false,
            "read_at" => null,
            "created_at" => date('Y-m-d H:i:s', strtotime('-4 hours'))
        ]
    ];
    
    file_put_contents($notificationsFile, json_encode($notifications, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "✅ Dados de notificações de exemplo criados\n";
}

echo "✅ Verificação concluída!\n";
echo "🚀 Sistema pronto para uso em: http://localhost:8000\n";
echo "👤 Login: admin@engenhario.com / password\n";
