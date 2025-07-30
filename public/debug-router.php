<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Router para servidor PHP embutido
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Log the request for debugging
error_log("Request URI: " . $requestUri);
error_log("Parsed path: " . $path);

// Se é um arquivo estático que existe, serve diretamente
if (file_exists(__DIR__ . $path) && is_file(__DIR__ . $path)) {
    error_log("Serving static file: " . __DIR__ . $path);
    return false; // Serve o arquivo estático
}

// Completely bypassing the original index.php to avoid auto_login.php issues
require_once __DIR__ . '/../autoload.php';

use App\Core\Router;
use App\Core\Auth;
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

// Start session
session_start();

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

// Support routes - IMPORTANTE: Rotas específicas primeiro
$router->get('/support', [SupportController::class, 'index'], [AuthMiddleware::class]);
$router->get('/support/create', [SupportController::class, 'create'], [AuthMiddleware::class]);
$router->post('/support/create', [SupportController::class, 'create'], [AuthMiddleware::class]);
// Removendo a rota /support/{id} pois está causando conflito com /support/view/{id}
// $router->get('/support/{id}', [SupportController::class, 'show'], [AuthMiddleware::class]);
$router->get('/support/view/{id}', [SupportController::class, 'show'], [AuthMiddleware::class]);

// Query string based routes for support
$router->get('/support-ticket', function() {
    $id = $_GET['id'] ?? null;
    error_log("Support ticket access via query string. ID: " . ($id ?? 'null'));
    
    if (!$id) {
        header('Location: /support');
        exit;
    }
    
    // Create support controller and call show method
    $controller = new App\Controllers\SupportController();
    $controller->show($id);
}, [AuthMiddleware::class]);

// Route for replying to a ticket via query string
$router->post('/support/reply', function() {
    $id = $_GET['id'] ?? null;
    error_log("Support ticket reply via query string. ID: " . ($id ?? 'null'));
    
    if (!$id) {
        header('Location: /support');
        exit;
    }
    
    // Create support controller and call reply method
    $controller = new App\Controllers\SupportController();
    $controller->reply($id);
}, [AuthMiddleware::class]);

// For updating ticket status
$router->post('/support/update-status', function() {
    $id = $_GET['id'] ?? null;
    error_log("Support ticket status update via query string. ID: " . ($id ?? 'null'));
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
        exit;
    }
    
    // Create support controller and call updateStatus method
    $controller = new App\Controllers\SupportController();
    $controller->updateStatus($id);
}, [AuthMiddleware::class]);

// Direct ticket test endpoint
$router->get('/test-ticket', function() {
    $id = $_GET['id'] ?? null;
    error_log("Test ticket endpoint. ID: " . ($id ?? 'null'));
    
    if (!$id) {
        echo "No ID provided";
        exit;
    }
    
    require_once __DIR__ . '/support-ticket-test.php';
}, [AuthMiddleware::class]);

// Dispatch the request
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

$router->dispatch($requestUri, $requestMethod);
