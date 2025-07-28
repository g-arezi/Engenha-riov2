<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Engenha Rio - Sistema de Gestão de Projetos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
        }
        
        .home-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .home-content {
            text-align: center;
            max-width: 1200px;
            width: 100%;
        }
        
        .logo-section {
            margin-bottom: 50px;
        }
        
        .logo-title {
            font-size: 3.5rem;
            font-weight: 300;
            letter-spacing: 8px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .logo-subtitle {
            font-size: 1.2rem;
            font-weight: 300;
            color: #bdc3c7;
            margin-bottom: 60px;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 40px 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #3498db;
        }
        
        .feature-icon.users {
            color: #3498db;
        }
        
        .feature-icon.chart {
            color: #2ecc71;
        }
        
        .feature-icon.bell {
            color: #f39c12;
        }
        
        .feature-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .feature-description {
            font-size: 0.95rem;
            color: #ecf0f1;
            line-height: 1.6;
        }
        
        .actions-section {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 500;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-login {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
        }
        
        .btn-login:hover {
            background: linear-gradient(45deg, #2980b9, #3498db);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.4);
            color: white;
        }
        
        .btn-register {
            background: transparent;
            color: white;
            border: 2px solid #ecf0f1;
        }
        
        .btn-register:hover {
            background: #ecf0f1;
            color: #2c3e50;
            transform: translateY(-2px);
        }
        
        .footer-info {
            position: absolute;
            bottom: 20px;
            left: 20px;
            font-size: 0.9rem;
            color: #bdc3c7;
        }
        
        @media (max-width: 768px) {
            .logo-title {
                font-size: 2.5rem;
                letter-spacing: 4px;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .feature-card {
                padding: 30px 20px;
            }
            
            .actions-section {
                flex-direction: column;
                align-items: center;
            }
            
            .action-btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="home-container">
        <div class="home-content">
            <!-- Logo e Título -->
            <div class="logo-section">
                <h1 class="logo-title">ENGENHARIO</h1>
                <p class="logo-subtitle">Sistema Completo de Gestão de Documentos e Projetos de Arquitetura</p>
            </div>
            
            <!-- Cards de Funcionalidades -->
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="feature-title">Sistema de Usuários</h3>
                    <p class="feature-description">
                        3 tipos de usuários com controle de acesso diferenciado para gerenciar projetos de forma eficiente
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon chart">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">Dashboard Interativo</h3>
                    <p class="feature-description">
                        Gestão completa de projetos e documentos com visão em tempo real do progresso
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon bell">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3 class="feature-title">Notificações</h3>
                    <p class="feature-description">
                        Sistema automático de emails e alertas para manter todos atualizados sobre o andamento
                    </p>
                </div>
            </div>
            
            <!-- Botões de Ação -->
            <div class="actions-section">
                <a href="/auth/login" class="action-btn btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    Fazer Login
                </a>
                <a href="/auth/register" class="action-btn btn-register">
                    <i class="fas fa-user-plus"></i>
                    Criar Conta
                </a>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer-info">
        <strong>Desenvolvedor:</strong> Gabriel Ariza<br>
        <strong>Portfólio:</strong> <a href="#" style="color: #3498db;">Clique aqui</a>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
