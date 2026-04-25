# Laravel Octane com Swoole - Setup de Desenvolvimento

## 🚀 O que foi implementado

O projeto foi configurado para usar **Laravel Octane com Swoole** no ambiente de desenvolvimento, substituindo o stack tradicional PHP-FPM + Nginx.

### Principais mudanças:

#### 1. **Instalação do Laravel Octane**
- Pacote `laravel/octane` instalado via Composer
- Configuração padrão gerada em `config/octane.php`
- Server padrão configurado para `swoole`

#### 2. **Docker - Dockerfile atualizado**
- Extensão Swoole instalada via PECL
- Porta 8000 exposta (padrão do Octane)
- CMD alterado para `octane` (inicialização automática)

#### 3. **Docker Compose**
- Container `nginx` removido (não é mais necessário)
- Container `app` agora expõe a porta 8000 diretamente
- Mapeamento de porta: `localhost:80` → `container:8000`

#### 4. **Entrypoint otimizado**
- Script `entrypoint.sh` adaptado para iniciar o Octane
- Suporte a modo watch opcional (requer `chokidar`)
- Configurável via variável `OCTANE_WATCH=true`

#### 5. **PHP.ini otimizado**
- OPcache JIT habilitado (`opcache.jit=tracing`)
- JIT buffer de 128MB para melhor performance

## 📦 Como usar

### Iniciar o ambiente
```bash
docker compose up -d
```

### Verificar logs
```bash
docker compose logs -f app
```

### Acessar a aplicação
```
http://localhost
```

### Parar o ambiente
```bash
docker compose down
```

## 🔧 Configurações avançadas

### Habilitar modo Watch (auto-reload em desenvolvimento)

1. Instalar o chokidar:
```bash
docker compose exec app npm install --save-dev chokidar
```

2. Adicionar variável de ambiente no `docker-compose.yml`:
```yaml
services:
  app:
    environment:
      - OCTANE_WATCH=true
```

3. Reiniciar container:
```bash
docker compose restart app
```

### Ajustar workers e threads (Swoole)

Edite `config/octane.php`:

```php
'swoole' => [
    'options' => [
        'http_compression' => true,
        'http_compression_level' => 6,
        'compression_min_length' => 20,
        'package_max_length' => 10 * 1024 * 1024,
        'open_http2_protocol' => true,
        'worker_num' => swoole_cpu_num() * 2,
        'task_worker_num' => swoole_cpu_num() * 2,
    ],
],
```

## ⚡ Performance

### Benefícios do Octane/Swoole:

- **3-5x mais rápido** que PHP-FPM tradicional
- Aplicação Laravel mantida em memória entre requisições
- Workers persistentes reduzem overhead de bootstrap
- Suporte nativo a WebSockets e tasks assíncronas
- HTTP/2 habilitado por padrão

### Benchmark rápido:
```bash
# Com Octane/Swoole
ab -n 1000 -c 10 http://localhost/

# Espere ~3-5x mais requests/segundo comparado ao setup anterior
```

## 📝 Arquivos modificados

- `docker/php/Dockerfile` - Instalação do Swoole
- `docker/php/entrypoint.sh` - Inicialização do Octane
- `docker/php/php.ini` - Otimizações JIT
- `docker-compose.yml` - Remoção do Nginx, exposição porta 8000
- `config/octane.php` - Server padrão = swoole
- `composer.json` - Adição do laravel/octane

## ⚠️ Considerações importantes

### O que funciona diferente no Octane:

1. **Estado compartilhado**: Variáveis estáticas e singletons persistem entre requests
2. **Memory leaks**: Cuidado com referências circulares
3. **File uploads**: Funciona normalmente, mas arquivos são limpos automaticamente
4. **Sessions**: Funcionam normalmente com Redis/Database drivers

### Boas práticas:

- Use listeners no `config/octane.php` para limpar estado entre requests
- Evite variáveis globais e estáticas
- Teste memory leaks em ambientes de staging
- Use `octane:reload` ao alterar código em produção

## 🔄 Próximos passos (produção)

Para produção, será necessário:

1. Atualizar `docker-compose.prod.yml` similarmente
2. Ajustar `docker/php/Dockerfile.prod`
3. Configurar supervisor para gerenciar workers do Octane
4. Ajustar número de workers baseado em CPU disponível
5. Configurar healthchecks apropriados
6. Implementar estratégia de reload sem downtime

## 📚 Recursos

- [Laravel Octane Docs](https://laravel.com/docs/11.x/octane)
- [Swoole Docs](https://www.swoole.co.uk/)
- [Performance Tuning Guide](https://laravel.com/docs/11.x/octane#swoole)
