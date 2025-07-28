<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$title = 'Cadastro - Engenha Rio';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-image {
            max-width: 250px;
            width: 100%;
            height: auto;
            margin-bottom: 10px;
            filter: brightness(1.1) contrast(1.05);
        }
        
        .logo-subtitle {
            font-size: 0.9rem;
            color: #bdc3c7;
            margin-bottom: 0;
        }
        
        .alert {
            background: rgba(52, 152, 219, 0.2);
            border: 1px solid #3498db;
            color: #3498db;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 12px 15px;
            font-size: 0.9rem;
        }
        
        .alert i {
            margin-right: 8px;
        }
        
        .form-label {
            color: white;
            font-weight: 500;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            color: white;
            padding: 12px 15px;
            margin-bottom: 15px;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            color: white;
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .btn-register {
            background: linear-gradient(45deg, #3498db, #2980b9);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            padding: 12px;
            width: 100%;
            margin-bottom: 15px;
            margin-top: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            background: linear-gradient(45deg, #2980b9, #3498db);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.4);
        }
        
        .login-link {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .login-link p {
            color: #bdc3c7;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        
        .btn-login {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            color: white;
            font-weight: 500;
            padding: 10px;
            width: 100%;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-login:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
            text-decoration: none;
        }
        
        .footer-info {
            text-align: center;
            color: #7f8c8d;
            font-size: 0.8rem;
            margin-top: 20px;
        }
        
        .error-alert {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid #e74c3c;
            color: #e74c3c;
        }
        
        .success-alert {
            background: rgba(46, 204, 113, 0.2);
            border: 1px solid #2ecc71;
            color: #2ecc71;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <!-- Logo e Título -->
        <div class="logo-section">
            <img src="/assets/images/engenhario-logo.png" alt="Engenha Rio" class="logo-image">
            <p class="logo-subtitle">Sistema de Gestão de Documentos</p>
        </div>
        
        <!-- Alerta de Informação -->
        <div class="alert">
            <i class="fas fa-info-circle"></i>
            Todos os novos usuários são registrados como <strong>Cliente</strong>. Para alterar permissões, entre em contato com um administrador.
        </div>
        
        <!-- Alertas de Erro/Sucesso -->
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error-alert" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success-alert" role="alert">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <!-- Formulário de Cadastro -->
        <form method="POST" action="/auth/register">
            <div class="mb-3">
                <label for="name" class="form-label">
                    <i class="fas fa-user"></i> Nome Completo
                </label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="fas fa-lock"></i> Senha
                </label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <div class="mb-3">
                <label for="password_confirmation" class="form-label">
                    <i class="fas fa-lock"></i> Confirmar Senha
                </label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
            </div>
            
            <button type="submit" class="btn-register">
                <i class="fas fa-user-plus me-2"></i> Criar Conta
            </button>
        </form>
        
        <!-- Link Fazer Login -->
        <div class="login-link">
            <p>Já tem uma conta?</p>
            <a href="/auth/login" class="btn-login">
                <i class="fas fa-sign-in-alt me-2"></i> Fazer Login
            </a>
        </div>
        
        <!-- Rodapé -->
        <div class="footer-info">
            © 2025 ENGENHARIO. Todos os direitos reservados.
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
