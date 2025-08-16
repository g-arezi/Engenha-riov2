# Configurações de Upload de Arquivos Grandes

## Configurações Implementadas

### Servidor PHP Integrado (php -S)
Para o servidor PHP integrado, criamos um arquivo `php.ini` personalizado na raiz do projeto com as seguintes configurações:

```ini
[PHP]
upload_max_filesize = 2048M
post_max_size = 2048M
memory_limit = 3072M
max_execution_time = 600
max_input_time = 600
```

Para iniciar o servidor com estas configurações, use o comando:
```bash
php -c c:\Users\Usuario\Desktop\Engenha-riov2\php.ini -S localhost:8000 -t public
```

### Servidor Apache (Produção)
Para servidores Apache, as configurações de upload foram modificadas no arquivo `.htaccess` na pasta `public`:

```apache
<IfModule mod_php.c>
    php_value upload_max_filesize 2048M
    php_value post_max_size 2048M
    php_value max_execution_time 600
    php_value max_input_time 600
    php_value memory_limit 3072M
    php_flag display_errors Off
    php_flag log_errors On
</IfModule>
```

## Verificação das Configurações

Você pode verificar se as configurações foram aplicadas acessando:
http://localhost:8000/test-upload-limit.php

## Considerações Adicionais

1. **Servidor de Produção**: Ao implantar em produção, pode ser necessário modificar o `php.ini` global do servidor.
2. **Timeout do Cliente**: Para arquivos muito grandes, considere aumentar também o timeout no lado do cliente.
3. **Processamento em Partes**: Para arquivos extremamente grandes, considere implementar upload em partes (chunks).
