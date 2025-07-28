<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Simples - Engenha Rio</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 500px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5; 
        }
        .form-container { 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .form-group { 
            margin-bottom: 15px; 
        }
        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold; 
        }
        input, select { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            box-sizing: border-box; 
        }
        button { 
            width: 100%; 
            padding: 12px; 
            background: #007bff; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            font-size: 16px; 
            cursor: pointer; 
        }
        button:hover { 
            background: #0056b3; 
        }
        .alert { 
            padding: 10px; 
            margin: 10px 0; 
            border-radius: 4px; 
        }
        .alert-success { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
        }
        .alert-danger { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Upload Simples - Engenha Rio</h2>
        
        <?php
        // Exibir mensagens de erro ou sucesso
        if (isset($_GET['error'])) {
            echo '<div class="alert alert-danger">Erro: ' . htmlspecialchars($_GET['error']) . '</div>';
        }
        if (isset($_GET['success'])) {
            echo '<div class="alert alert-success">Sucesso: Arquivo enviado com sucesso!</div>';
        }
        ?>
        
        <form method="POST" action="/documents/upload" enctype="multipart/form-data">
            <div class="form-group">
                <label for="document">Selecionar Arquivo:</label>
                <input type="file" id="document" name="document" required>
            </div>
            
            <div class="form-group">
                <label for="name">Nome do Documento:</label>
                <input type="text" id="name" name="name" placeholder="Nome personalizado (opcional)">
            </div>
            
            <div class="form-group">
                <label for="description">Descrição:</label>
                <input type="text" id="description" name="description" placeholder="Descrição do documento">
            </div>
            
            <div class="form-group">
                <label for="category">Categoria:</label>
                <select id="category" name="category">
                    <option value="">Selecione uma categoria</option>
                    <option value="projeto">Projeto</option>
                    <option value="contrato">Contrato</option>
                    <option value="relatorio">Relatório</option>
                    <option value="documento">Documento Geral</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="project_id">ID do Projeto:</label>
                <input type="text" id="project_id" name="project_id" placeholder="projeto_1" value="projeto_1">
            </div>
            
            <button type="submit">📤 Enviar Arquivo</button>
        </form>
        
        <p style="margin-top: 20px; font-size: 12px; color: #666;">
            <strong>Formatos aceitos:</strong> PDF, DOC, DOCX, JPG, PNG, XLS, XLSX<br>
            <strong>Tamanho máximo:</strong> 40MB
        </p>
        
        <p style="margin-top: 10px;">
            <a href="/documents" style="color: #007bff;">← Voltar para Documentos</a> | 
            <a href="/test-upload.html" style="color: #007bff;">Teste Avançado</a>
        </p>
    </div>
</body>
</html>
