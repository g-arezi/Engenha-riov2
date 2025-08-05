<?php

// Try to load Composer autoloader, fallback to custom autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../autoload.php';
}

use App\Core\Router;
use App\Core\Auth;
use App\Core\HostingerHelper;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\AdminController;
use App\Controllers\TemplateController;
use App\Controllers\ProjectController;
use App\Controllers\DocumentController;
use App\Controllers\DocumentWorkflowController;
use App\Controllers\SupportController;
use App\Controllers\NotificationController;
use App\Middleware\AuthMiddleware;

// Load configuration
$config = require_once __DIR__ . '/../config/app.php';

// Configure for Hostinger if needed
App\Core\HostingerHelper::setupUrlHandling();

// Adjust configuration for Hostinger if needed
$config = App\Core\HostingerHelper::configureForHostinger($config);

// Configure session
session_name($config['session']['name']);
session_set_cookie_params(
    $config['session']['lifetime'],
    '/',
    '',
    $config['session']['secure'],
    $config['session']['httponly']
);

// Use default session path or custom path if specified in config
if (isset($config['session']['save_path']) && !empty($config['session']['save_path'])) {
    $sessionPath = $config['session']['save_path'];
} else {
    // Default to a directory in our application that should be writable
    $sessionPath = __DIR__ . '/../data/sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0755, true);
    }
}
session_save_path($sessionPath);

// Start the session with error suppression to avoid warnings on shared hosting
@session_start();

// Auto login has been completely disabled for testing
// No auto-login is used anymore

// Create router instance
$router = new Router();

// Home page (public)
$router->get('/home', function() {
    require_once __DIR__ . '/../views/home/index.php';
});

// Auth routes
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/auth/login', [AuthController::class, 'loginForm']);
$router->post('/auth/login', [AuthController::class, 'login']);
$router->get('/auth/register', [AuthController::class, 'registerForm']);
$router->post('/auth/register', [AuthController::class, 'register']);

// Protected routes
$router->get('/', function() {
    if (App\Core\Auth::check()) {
        header('Location: /dashboard');
        exit;
    } else {
        require_once __DIR__ . '/../views/home/index.php';
    }
});
$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$router->get('/profile', [AuthController::class, 'profile'], [AuthMiddleware::class]);
$router->post('/profile', [AuthController::class, 'updateProfile'], [AuthMiddleware::class]);

// Admin routes
$router->get('/admin', [AdminController::class, 'index'], [AuthMiddleware::class]);
$router->get('/admin/permissions', [AdminController::class, 'permissions'], [AuthMiddleware::class]);
$router->post('/admin/permissions', [AdminController::class, 'permissions'], [AuthMiddleware::class]);
$router->get('/admin/documents', [AdminController::class, 'documents'], [AuthMiddleware::class]);
$router->get('/admin/documents/create', [AdminController::class, 'createDocument'], [AuthMiddleware::class]);
$router->post('/admin/documents/create', [AdminController::class, 'createDocument'], [AuthMiddleware::class]);
$router->get('/admin/documents/{id}/edit', [AdminController::class, 'editDocument'], [AuthMiddleware::class]);
$router->post('/admin/documents/{id}/edit', [AdminController::class, 'editDocument'], [AuthMiddleware::class]);
$router->delete('/admin/documents/{id}', [AdminController::class, 'deleteDocument'], [AuthMiddleware::class]);

// User management routes
$router->get('/admin/users/create', [AdminController::class, 'createUser'], [AuthMiddleware::class]);
$router->post('/admin/users/create', [AdminController::class, 'createUser'], [AuthMiddleware::class]);
$router->get('/admin/users/{id}/edit', [AdminController::class, 'editUser'], [AuthMiddleware::class]);
$router->post('/admin/users/{id}/edit', [AdminController::class, 'editUser'], [AuthMiddleware::class]);
$router->delete('/admin/users/{id}', [AdminController::class, 'deleteUser'], [AuthMiddleware::class]);
$router->post('/admin/users/{id}/approve', [AdminController::class, 'approveUser'], [AuthMiddleware::class]);
$router->post('/admin/users/{id}/reject', [AdminController::class, 'rejectUser'], [AuthMiddleware::class]);
$router->delete('/admin/delete-user/{id}', [AdminController::class, 'deleteUser'], [AuthMiddleware::class]);
$router->post('/admin/update-user-status/{id}', [AdminController::class, 'updateUserStatus'], [AuthMiddleware::class]);
$router->get('/admin/edit-user/{id}', [AdminController::class, 'editUser'], [AuthMiddleware::class]);

// Template management routes
$router->get('/admin/templates', [TemplateController::class, 'index'], [AuthMiddleware::class]);
$router->get('/admin/templates/create', [TemplateController::class, 'create'], [AuthMiddleware::class]);
$router->post('/admin/templates/create', [TemplateController::class, 'create'], [AuthMiddleware::class]);
$router->get('/admin/templates/{id}/edit', [TemplateController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/admin/templates/{id}/edit', [TemplateController::class, 'edit'], [AuthMiddleware::class]);
$router->delete('/admin/templates/{id}/delete', [TemplateController::class, 'delete'], [AuthMiddleware::class]);

// Login slide management routes
$router->get('/admin/login-slides', [App\Controllers\LoginSlideController::class, 'index'], [AuthMiddleware::class]);
$router->get('/admin/login-slides/create', [App\Controllers\LoginSlideController::class, 'create'], [AuthMiddleware::class]);
$router->post('/admin/login-slides/create', [App\Controllers\LoginSlideController::class, 'store'], [AuthMiddleware::class]);
$router->get('/admin/login-slides/edit/{id}', [App\Controllers\LoginSlideController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/admin/login-slides/edit/{id}', [App\Controllers\LoginSlideController::class, 'update'], [AuthMiddleware::class]);
$router->post('/admin/login-slides/delete/{id}', [App\Controllers\LoginSlideController::class, 'delete'], [AuthMiddleware::class]);
$router->post('/admin/login-slides/order', [App\Controllers\LoginSlideController::class, 'updateOrder'], [AuthMiddleware::class]);
$router->post('/admin/login-slides/toggle/{id}', [App\Controllers\LoginSlideController::class, 'toggleStatus'], [AuthMiddleware::class]);

// Project routes - IMPORTANTE: Rotas mais específicas primeiro, depois as mais gerais com parâmetros
$router->get('/projects', [ProjectController::class, 'index'], [AuthMiddleware::class]);
$router->get('/projects/create', [ProjectController::class, 'create'], [AuthMiddleware::class]);
$router->post('/projects/create', [ProjectController::class, 'create'], [AuthMiddleware::class]);
$router->get('/projects/view/{id}', [ProjectController::class, 'show'], [AuthMiddleware::class]);
$router->post('/projects/update-status/{id}', [ProjectController::class, 'updateStatus'], [AuthMiddleware::class]);
// Rotas com parâmetros devem vir depois das mais específicas
$router->get('/projects/{id}/edit', [ProjectController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/projects/{id}/edit', [ProjectController::class, 'edit'], [AuthMiddleware::class]);
$router->delete('/projects/{id}', [ProjectController::class, 'delete'], [AuthMiddleware::class]);
$router->get('/projects/{id}', [ProjectController::class, 'show'], [AuthMiddleware::class]);

// Document routes
$router->get('/documents', [DocumentController::class, 'index'], [AuthMiddleware::class]);
$router->get('/documents/upload', [DocumentController::class, 'upload'], [AuthMiddleware::class]);
$router->post('/documents/upload', [DocumentController::class, 'upload'], [AuthMiddleware::class]);
$router->get('/documents/{id}/view', [DocumentController::class, 'view'], [AuthMiddleware::class]);
$router->get('/documents/{id}/download', [DocumentController::class, 'download'], [AuthMiddleware::class]);
$router->get('/documents/{id}/edit', [DocumentController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/documents/{id}/edit', [DocumentController::class, 'edit'], [AuthMiddleware::class]);
$router->delete('/documents/{id}', [DocumentController::class, 'delete'], [AuthMiddleware::class]);
$router->post('/documents/track-download/{id}', [DocumentController::class, 'trackDownload'], [AuthMiddleware::class]);

// Categories routes
$router->get('/categories/list', [DocumentController::class, 'listCategories'], [AuthMiddleware::class]);
$router->post('/categories/add', [DocumentController::class, 'addCategory'], [AuthMiddleware::class]);
$router->post('/categories/update/{id}', [DocumentController::class, 'updateCategory'], [AuthMiddleware::class]);
$router->delete('/categories/delete/{id}', [DocumentController::class, 'deleteCategory'], [AuthMiddleware::class]);

// Document Workflow routes
$router->get('/documents/project/{id}', [DocumentWorkflowController::class, 'projectDocuments'], [AuthMiddleware::class]);
$router->get('/documents/project/upload', [DocumentWorkflowController::class, 'uploadDocument'], [AuthMiddleware::class]);
$router->post('/documents/project/upload', [DocumentWorkflowController::class, 'uploadDocument'], [AuthMiddleware::class]);
$router->post('/documents/{id}/approve', [DocumentWorkflowController::class, 'approveDocument'], [AuthMiddleware::class]);
$router->post('/documents/{id}/reject', [DocumentWorkflowController::class, 'rejectDocument'], [AuthMiddleware::class]);
$router->post('/documents/update-status', [DocumentWorkflowController::class, 'updateDocumentStatus'], [AuthMiddleware::class]);
$router->get('/documents/project/{id}/download', [DocumentWorkflowController::class, 'downloadDocument'], [AuthMiddleware::class]);
$router->delete('/documents/project/{id}', [DocumentWorkflowController::class, 'deleteDocument'], [AuthMiddleware::class]);
$router->get('/documents/project/{id}/info', [DocumentWorkflowController::class, 'getDocumentInfo'], [AuthMiddleware::class]);

// Project workflow management routes (AJAX)
$router->post('/document-workflow/update-stage', [DocumentWorkflowController::class, 'updateStage'], [AuthMiddleware::class]);
$router->post('/document-workflow/update-status', [DocumentWorkflowController::class, 'updateStatus'], [AuthMiddleware::class]);
$router->post('/document-workflow/advance', [DocumentWorkflowController::class, 'advance'], [AuthMiddleware::class]);
$router->post('/document-workflow/revert', [DocumentWorkflowController::class, 'revert'], [AuthMiddleware::class]);
$router->post('/document-workflow/finalize', [DocumentWorkflowController::class, 'finalize'], [AuthMiddleware::class]);
$router->post('/document-workflow/approve-document-ajax', [DocumentWorkflowController::class, 'approveDocumentAjax'], [AuthMiddleware::class]);
$router->post('/document-workflow/reject-document-ajax', [DocumentWorkflowController::class, 'rejectDocumentAjax'], [AuthMiddleware::class]);

// Additional document upload route for AJAX drag-and-drop functionality
$router->post('/documents/upload-project-file', [DocumentWorkflowController::class, 'uploadProjectFile'], [AuthMiddleware::class]);
$router->post('/documents/handle-drag-drop', [DocumentWorkflowController::class, 'handleDragDropUpload'], [AuthMiddleware::class]);

// Support routes
$router->get('/support', [SupportController::class, 'index'], [AuthMiddleware::class]);
$router->get('/support/create', [SupportController::class, 'create'], [AuthMiddleware::class]);
$router->post('/support/create', [SupportController::class, 'create'], [AuthMiddleware::class]);
$router->get('/support/{id}', [SupportController::class, 'show'], [AuthMiddleware::class]);
$router->get('/support/view/{id}', [SupportController::class, 'show'], [AuthMiddleware::class]);
$router->post('/support/{id}/reply', [SupportController::class, 'reply'], [AuthMiddleware::class]);
$router->post('/support/update-status/{id}', [SupportController::class, 'updateStatus'], [AuthMiddleware::class]);

// Search and Export routes
$router->get('/search', [DocumentController::class, 'search'], [AuthMiddleware::class]);
$router->get('/export/{type}', [DocumentController::class, 'export'], [AuthMiddleware::class]);
$router->post('/import/data', [DocumentController::class, 'import'], [AuthMiddleware::class]);

// Dashboard API routes
$router->get('/dashboard/chart-data', [DashboardController::class, 'getChartData'], [AuthMiddleware::class]);

// Notification routes
$router->get('/notifications', [NotificationController::class, 'index'], [AuthMiddleware::class]);  
$router->post('/notifications/mark-read/{id}', [NotificationController::class, 'markAsRead'], [AuthMiddleware::class]);
$router->post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'], [AuthMiddleware::class]);
$router->get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'], [AuthMiddleware::class]);

// Dispatch the request
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

$router->dispatch($requestUri, $requestMethod);
