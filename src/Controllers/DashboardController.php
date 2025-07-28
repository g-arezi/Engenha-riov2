<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;

class DashboardController
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function index(): void
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        $user = Auth::user();
        
        // Estatísticas do dashboard
        $stats = $this->getDashboardStats();
        
        // Projetos recentes
        $recentProjects = $this->getRecentProjects();

        require_once __DIR__ . '/../../views/dashboard/index.php';
    }

    private function getDashboardStats(): array
    {
        $projects = $this->db->findAll('projects');
        $documents = $this->db->findAll('documents');

        $totalProjects = count($projects);
        $activeProjects = count(array_filter($projects, fn($p) => $p['status'] === 'ativo'));
        $pendingDocs = count(array_filter($documents, fn($d) => $d['status'] === 'pendente'));
        $finishedProjects = count(array_filter($projects, fn($p) => $p['status'] === 'finalizado'));

        return [
            'total_projects' => $totalProjects,
            'active_projects' => $activeProjects,
            'pending_docs' => $pendingDocs,
            'finished_projects' => $finishedProjects
        ];
    }

    private function getRecentProjects(): array
    {
        $projects = $this->db->findAll('projects');
        
        // Ordenar por data de criação (mais recentes primeiro)
        usort($projects, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return array_slice($projects, 0, 10); // Últimos 10 projetos
    }
}
