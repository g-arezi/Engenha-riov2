<?php
use App\Core\Auth;

$title = 'Cadastro - Engenha Rio';
$showSidebar = false;
$showNavbar = false;

ob_start();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Engenha Rio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .register-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        
        .register-header {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .register-header h2 {
            font-size: 1.8rem;
            font-weight: 300;
            margin-bottom: 10px;
            letter-spacing: 2px;
        }
        
        .register-header p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }
        
        .register-body {
            padding: 40px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-floating input,
        .form-floating select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            transition: border-color 0.3s ease;
        }
        
        .form-floating input:focus,
        .form-floating select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .form-floating label {
            color: #6c757d;
        }
        
        .btn-register {
            background: linear-gradient(45deg, #3498db, #2980b9);
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-weight: 500;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-register:hover {
            background: linear-gradient(45deg, #2980b9, #3498db);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.4);
        }
        
        .register-footer {
            text-align: center;
            padding: 20px 40px 40px;
            border-top: 1px solid #e9ecef;
        }
        
        .register-footer a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-footer a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .info-box {
            background: #e8f4fd;
            border: 1px solid #bee5eb;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .info-box .info-title {
            font-weight: 600;
            color: #0c5460;
            margin-bottom: 8px;
        }
        
        .info-box .info-text {
            font-size: 0.9rem;
            color: #0c5460;
            margin: 0;
        }
        
        @media (max-width: 576px) {
            .register-body {
                padding: 30px 20px;
            }
            
            .register-header {
                padding: 20px;
            }
            
            .register-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h2>CRIAR CONTA</h2>
                <p>Cadastre-se para acessar o sistema</p>
            </div>
            
            <div class="register-body">
                <!-- Alertas -->
                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <!-- Informação sobre aprovação -->
                <div class="info-box">
                    <div class="info-title">
                        <i class="fas fa-info-circle me-1"></i>
                        Informação Importante
                    </div>
                    <p class="info-text">
                        Sua conta será criada com status "pendente" e precisará ser aprovada por um Administrador ou Coordenador antes de poder acessar o sistema.
                    </p>
                </div>
                
                <form method="POST" action="/auth/register" id="registerForm">
                    <!-- Nome Completo -->
                    <div class="form-floating">
                        <input type="text" class="form-control" id="name" name="name" 
                               placeholder="Nome completo" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        <label for="name">
                            <i class="fas fa-user me-1"></i>
                            Nome Completo
                        </label>
                    </div>
                    
                    <!-- Email -->
                    <div class="form-floating">
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="email@exemplo.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        <label for="email">
                            <i class="fas fa-envelope me-1"></i>
                            E-mail
                        </label>
                    </div>
                    
                    <!-- Telefone -->
                    <div class="form-floating">
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               placeholder="(11) 99999-9999" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        <label for="phone">
                            <i class="fas fa-phone me-1"></i>
                            Telefone (opcional)
                        </label>
                    </div>
                    
                    <!-- Empresa/Organização -->
                    <div class="form-floating">
                        <input type="text" class="form-control" id="company" name="company" 
                               placeholder="Nome da empresa" value="<?= htmlspecialchars($_POST['company'] ?? '') ?>">
                        <label for="company">
                            <i class="fas fa-building me-1"></i>
                            Empresa/Organização (opcional)
                        </label>
                    </div>
                    
                    <!-- Tipo de Usuário Solicitado -->
                    <div class="form-floating">
                        <select class="form-select" id="requested_role" name="requested_role" required>
                            <option value="">Selecione o tipo de acesso</option>
                            <option value="cliente" <?= (($_POST['requested_role'] ?? '') === 'cliente') ? 'selected' : '' ?>>
                                Cliente - Para enviar documentos e acompanhar projetos
                            </option>
                            <option value="analista" <?= (($_POST['requested_role'] ?? '') === 'analista') ? 'selected' : '' ?>>
                                Analista - Para analisar e aprovar documentos
                            </option>
                        </select>
                        <label for="requested_role">
                            <i class="fas fa-user-tag me-1"></i>
                            Tipo de Acesso Solicitado
                        </label>
                    </div>
                    
                    <!-- Senha -->
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Senha" required minlength="6">
                        <label for="password">
                            <i class="fas fa-lock me-1"></i>
                            Senha (mínimo 6 caracteres)
                        </label>
                    </div>
                    
                    <!-- Confirmar Senha -->
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" 
                               placeholder="Confirmar senha" required minlength="6">
                        <label for="password_confirmation">
                            <i class="fas fa-lock me-1"></i>
                            Confirmar Senha
                        </label>
                    </div>
                    
                    <!-- Botão de Cadastro -->
                    <button type="submit" class="btn btn-primary btn-register">
                        <i class="fas fa-user-plus me-2"></i>
                        Criar Conta
                    </button>
                </form>
            </div>
            
            <div class="register-footer">
                <p class="mb-0">
                    Já tem uma conta? 
                    <a href="/auth/login">Fazer Login</a>
                </p>
                <p class="mt-2 mb-0">
                    <a href="/">
                        <i class="fas fa-home me-1"></i>
                        Voltar ao Início
                    </a>
                </p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação de senhas
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const passwordConfirmation = document.getElementById('password_confirmation').value;
            
            if (password !== passwordConfirmation) {
                e.preventDefault();
                alert('As senhas não coincidem. Por favor, verifique e tente novamente.');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('A senha deve ter pelo menos 6 caracteres.');
                return false;
            }
        });
        
        // Máscara para telefone
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
            value = value.replace(/(\d)(\d{4})$/, '$1-$2');
            e.target.value = value;
        });
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
echo $content;
?>
