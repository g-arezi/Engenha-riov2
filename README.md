# Engenha Rio - Sistema de Gestão de Projetos

## 🚀 Instalação e Configuração

### Pré-requisitos
- PHP 8.0 ou superior
- Compositor (Composer)
- Servidor web (Apache/Nginx) ou PHP built-in server

### Passo 1: Clonar/Baixar o projeto
```bash
git clone <repositorio> engenha-rio
cd engenha-rio
```

### Passo 2: Instalar dependências
```bash
composer install
```

### Passo 3: Configurar permissões
```bash
# Linux/Mac
chmod -R 775 data/
chmod -R 775 public/uploads/

# Windows (PowerShell como Administrador)
icacls data /grant Users:F /T
icacls public\uploads /grant Users:F /T
```

### Passo 4: Executar o servidor
```bash
composer serve
# ou
php -S localhost:8000 -t public
```

### Passo 5: Acessar o sistema
Abra seu navegador e acesse: `http://localhost:8000`

## 👤 Login Padrão

### Administrador
- **Email:** admin@engenhario.com
- **Senha:** password

### Analista
- **Email:** rafael@engenhario.com  
- **Senha:** password

## 📁 Estrutura do Projeto

```
engenha-rio/
├── 📂 config/              # Configurações
├── 📂 data/                # Banco de dados JSON
├── 📂 public/              # Arquivos públicos
│   ├── 📂 assets/          # CSS, JS, imagens
│   ├── 📂 uploads/         # Uploads de arquivos
│   └── index.php           # Ponto de entrada
├── 📂 src/                 # Código fonte
│   ├── 📂 Controllers/     # Controladores
│   ├── 📂 Core/           # Classes principais
│   └── 📂 Middleware/     # Middlewares
├── 📂 views/              # Templates
│   ├── 📂 auth/           # Autenticação
│   ├── 📂 dashboard/      # Dashboard
│   ├── 📂 admin/          # Administração
│   ├── 📂 documents/      # Documentos
│   ├── 📂 support/        # Suporte
│   └── 📂 layouts/        # Layouts base
└── composer.json          # Dependências
```

## ⚙️ Configuração

### Banco de Dados
O sistema utiliza arquivos JSON como banco de dados, localizados na pasta `data/`:
- `users.json` - Usuários do sistema
- `projects.json` - Projetos
- `documents.json` - Documentos
- `document_templates.json` - Templates de documentos
- `support_tickets.json` - Tickets de suporte

### Upload de Arquivos
- Tamanho máximo: 10MB
- Formatos permitidos: PDF, DOC, DOCX, JPG, JPEG, PNG, XLS, XLSX
- Pasta de destino: `public/uploads/`

## 🎨 Personalização

### Cores e Tema
Edite o arquivo `public/assets/css/app.css` para personalizar:
- Cores principais (variáveis CSS no `:root`)
- Layout da sidebar
- Estilos dos componentes

### Logo e Branding
- Substitua o texto "ENGENHARIO" nos templates
- Adicione seu logo em `public/assets/images/`
- Atualize os layouts em `views/layouts/`

## 🔒 Segurança

### Senhas
As senhas são hasheadas usando `password_hash()` do PHP.
Para gerar uma nova senha hash:
```php
echo password_hash('sua_senha', PASSWORD_DEFAULT);
```

### Permissões
O sistema possui 4 níveis de usuário:
- **Administrador**: Acesso total
- **Analista**: Gestão de projetos e documentos
- **Coordenador**: Gestão de projetos e documentos
- **Cliente**: Visualização de projetos e upload de documentos

## 🚦 Status do Sistema

### Dashboard
Mostra métricas em tempo real:
- Total de projetos
- Projetos ativos
- Documentos pendentes
- Projetos finalizados

### Funcionalidades Implementadas
- ✅ Sistema de autenticação
- ✅ Dashboard interativo
- ✅ Gestão de usuários (Admin)
- ✅ Sistema de documentos
- ✅ Interface de suporte
- ✅ Permissões por perfil
- ✅ Layout responsivo

## 🐛 Troubleshooting

### Problemas Comuns

**Erro de permissão nos arquivos**
```bash
chmod -R 775 data/ public/uploads/
```

**Erro 500 - Internal Server Error**
- Verifique os logs do PHP
- Certifique-se que as dependências estão instaladas
- Verifique as permissões dos arquivos

**Problemas de upload**
- Verifique o tamanho máximo do arquivo (10MB)
- Certifique-se que a pasta `uploads/` existe e tem permissão de escrita

## 📞 Suporte

Para suporte técnico ou dúvidas sobre implementação, utilize o sistema de tickets interno ou entre em contato com a equipe de desenvolvimento.

## 📄 Licença

Este projeto é propriedade da Engenha Rio. Todos os direitos reservados.

---

**Versão:** 1.0.0  
**Última atualização:** Janeiro 2025
