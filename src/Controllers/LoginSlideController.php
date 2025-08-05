<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;

class LoginSlideController
{
    private $db;
    
    public function __construct()
    {
        $this->db = new Database();
    }
    
    /**
     * Display the list of login slides
     */
    public function index(): void
    {
        // Check if user is admin
        if (!Auth::isAdmin()) {
            header('Location: /dashboard');
            exit;
        }
        
        $slides = $this->db->findAll('login_slides');
        
        // Sort by order
        usort($slides, function($a, $b) {
            return $a['order'] <=> $b['order'];
        });
        
        $title = 'Gerenciar Slides de Login';
        $content = require_once __DIR__ . '/../../views/admin/login_slides/index.php';
        require_once __DIR__ . '/../../views/layouts/app.php';
    }
    
    /**
     * Show the form to create a new slide
     */
    public function create(): void
    {
        // Check if user is admin
        if (!Auth::isAdmin()) {
            header('Location: /dashboard');
            exit;
        }
        
        $title = 'Adicionar Novo Slide de Login';
        $content = require_once __DIR__ . '/../../views/admin/login_slides/create.php';
        require_once __DIR__ . '/../../views/layouts/app.php';
    }
    
    /**
     * Store a new slide
     */
    public function store(): void
    {
        // Check if user is admin
        if (!Auth::isAdmin()) {
            header('Location: /dashboard');
            exit;
        }
        
        $type = $_POST['type'] ?? 'image';
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $active = isset($_POST['active']) ? true : false;
        
        // Handle file upload
        $url = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/assets/images/login/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $uploadPath = $uploadDir . $filename;
            
            // Move the uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $url = '/assets/images/login/' . $filename;
            } else {
                $_SESSION['error'] = 'Erro ao fazer upload da imagem.';
                header('Location: /admin/login-slides/create');
                exit;
            }
        } else if ($_POST['type'] === 'color') {
            $url = $_POST['color'] ?? '#2c3e50';
        } else {
            $_SESSION['error'] = 'É necessário enviar uma imagem ou selecionar uma cor.';
            header('Location: /admin/login-slides/create');
            exit;
        }
        
        // Get the highest order number
        $slides = $this->db->findAll('login_slides');
        $maxOrder = 0;
        foreach ($slides as $slide) {
            if ($slide['order'] > $maxOrder) {
                $maxOrder = $slide['order'];
            }
        }
        
        // Create slide data
        $slideData = [
            'id' => uniqid(),
            'type' => $type,
            'url' => $url,
            'title' => $title,
            'description' => $description,
            'active' => $active,
            'order' => $maxOrder + 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert into database
        $this->db->insert('login_slides', $slideData);
        
        $_SESSION['success'] = 'Slide adicionado com sucesso.';
        header('Location: /admin/login-slides');
        exit;
    }
    
    /**
     * Show the form to edit a slide
     */
    public function edit(string $id): void
    {
        // Check if user is admin
        if (!Auth::isAdmin()) {
            header('Location: /dashboard');
            exit;
        }
        
        $slide = $this->db->find('login_slides', $id);
        
        if (!$slide) {
            header('Location: /admin/login-slides');
            exit;
        }
        
        $title = 'Editar Slide de Login';
        $content = require_once __DIR__ . '/../../views/admin/login_slides/edit.php';
        require_once __DIR__ . '/../../views/layouts/app.php';
    }
    
    /**
     * Update a slide
     */
    public function update(string $id): void
    {
        // Check if user is admin
        if (!Auth::isAdmin()) {
            header('Location: /dashboard');
            exit;
        }
        
        $slide = $this->db->find('login_slides', $id);
        
        if (!$slide) {
            header('Location: /admin/login-slides');
            exit;
        }
        
        $type = $_POST['type'] ?? 'image';
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $active = isset($_POST['active']) ? true : false;
        
        // Handle file upload
        $url = $slide['url']; // Default to existing URL
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/assets/images/login/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $uploadPath = $uploadDir . $filename;
            
            // Move the uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                // Remove old image if it's not the default
                if ($slide['url'] !== '/assets/images/login/default.jpg' && file_exists(__DIR__ . '/../../public' . $slide['url'])) {
                    unlink(__DIR__ . '/../../public' . $slide['url']);
                }
                
                $url = '/assets/images/login/' . $filename;
            } else {
                $_SESSION['error'] = 'Erro ao fazer upload da imagem.';
                header('Location: /admin/login-slides/edit/' . $id);
                exit;
            }
        } else if ($_POST['type'] === 'color') {
            $url = $_POST['color'] ?? '#2c3e50';
        }
        
        // Update slide data
        $slideData = [
            'type' => $type,
            'url' => $url,
            'title' => $title,
            'description' => $description,
            'active' => $active,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Update in database
        $this->db->update('login_slides', $id, $slideData);
        
        $_SESSION['success'] = 'Slide atualizado com sucesso.';
        header('Location: /admin/login-slides');
        exit;
    }
    
    /**
     * Delete a slide
     */
    public function delete(string $id): void
    {
        // Check if user is admin
        if (!Auth::isAdmin()) {
            header('Location: /dashboard');
            exit;
        }
        
        $slide = $this->db->find('login_slides', $id);
        
        if (!$slide) {
            header('Location: /admin/login-slides');
            exit;
        }
        
        // Don't delete if it's the only slide
        $slides = $this->db->findAll('login_slides');
        if (count($slides) <= 1) {
            $_SESSION['error'] = 'Não é possível excluir o único slide do sistema.';
            header('Location: /admin/login-slides');
            exit;
        }
        
        // Remove image file if it's not the default
        if ($slide['url'] !== '/assets/images/login/default.jpg' && $slide['type'] === 'image' && file_exists(__DIR__ . '/../../public' . $slide['url'])) {
            unlink(__DIR__ . '/../../public' . $slide['url']);
        }
        
        // Delete from database
        $this->db->delete('login_slides', $id);
        
        $_SESSION['success'] = 'Slide excluído com sucesso.';
        header('Location: /admin/login-slides');
        exit;
    }
    
    /**
     * Update slide order
     */
    public function updateOrder(): void
    {
        // Check if user is admin
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $slidesOrder = $input['slides'] ?? [];
        
        if (empty($slidesOrder)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No slide order provided']);
            exit;
        }
        
        foreach ($slidesOrder as $index => $slideId) {
            $this->db->update('login_slides', $slideId, ['order' => $index + 1]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Ordem atualizada com sucesso']);
    }
    
    /**
     * Toggle slide status (active/inactive)
     */
    public function toggleStatus(string $id): void
    {
        // Check if user is admin
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $slide = $this->db->find('login_slides', $id);
        
        if (!$slide) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Slide not found']);
            exit;
        }
        
        // Make sure at least one slide is active
        if ($slide['active']) {
            $activeSlides = array_filter($this->db->findAll('login_slides'), function($s) {
                return $s['active'] === true;
            });
            
            if (count($activeSlides) <= 1) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Pelo menos um slide deve estar ativo']);
                exit;
            }
        }
        
        // Toggle status
        $this->db->update('login_slides', $id, [
            'active' => !$slide['active'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Status atualizado com sucesso',
            'active' => !$slide['active']
        ]);
    }
    
    /**
     * Get active slides for login page
     */
    public function getActiveSlides()
    {
        $slides = $this->db->findAll('login_slides', ['active' => true]);
        
        // Sort by order
        usort($slides, function($a, $b) {
            return $a['order'] <=> $b['order'];
        });
        
        return $slides;
    }
}
