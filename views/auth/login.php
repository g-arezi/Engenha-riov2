<?php
// Session should already be started in index.php, no need to start it again here
$title = 'Login - Engenha Rio';
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
        
        .login-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
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
            margin-bottom: 20px;
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
        
        .form-check {
            margin-bottom: 25px;
        }
        
        .form-check-input {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .form-check-input:checked {
            background-color: #3498db;
            border-color: #3498db;
        }
        
        .form-check-label {
            color: #bdc3c7;
            font-size: 0.9rem;
        }
        
        .btn-login {
            background: linear-gradient(45deg, #3498db, #2980b9);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            padding: 12px;
            width: 100%;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: linear-gradient(45deg, #2980b9, #3498db);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.4);
        }
        
        .forgot-password {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .forgot-password a {
            color: #3498db;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .register-link {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .register-link p {
            color: #bdc3c7;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        
        .btn-register {
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
        
        .btn-register:hover {
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
        
        .alert {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid #e74c3c;
            color: #e74c3c;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Logo e Título -->
        <div class="logo-section">
            <img src="/assets/images/engenhario-logo.png" alt="Engenha Rio" class="logo-image">
            <p class="logo-subtitle">Sistema de Gestão de Documentos</p>
        </div>
        
        <!-- Alertas de Erro -->
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <!-- Formulário de Login -->
        <form method="POST" action="/login">
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
            
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">
                    Lembrar-me
                </label>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt me-2"></i> Entrar
            </button>
        </form>
        
        <!-- Link Esqueci Senha -->
        <div class="forgot-password">
            <a href="#"><i class="fas fa-key"></i> Esqueci minha senha</a>
        </div>
        
        <!-- Link Criar Conta -->
        <div class="register-link">
            <p>Não tem uma conta?</p>
            <a href="/auth/register" class="btn-register">
                <i class="fas fa-user-plus me-2"></i> Criar Conta
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
