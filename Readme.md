# Laravel Docker Development Environment

Ambiente de desenvolvimento Docker completo para Laravel.

## 📦 Stack

| Serviço     | Versão    | Porta  |
|-------------|-----------|--------|
| PHP-FPM     | 8.4       | 9000   |
| Nginx       | Alpine    | 80     |
| MySQL       | 8.4       | 3306   |
| Redis       | 7 Alpine  | 6379   |
| Node.js     | 22        | —      |
| phpMyAdmin  | latest    | 8080   |
| Mailpit     | latest    | 8025   |

## 🚀 Quick Start

### 1. Criar o projeto Laravel

```bash
# Build das imagens
docker compose build

# Criar projeto Laravel (primeira vez)
docker compose run --rm app composer create-project laravel/laravel .
```

### 2. Configurar o `.env` do Laravel

Edite o arquivo `.env` gerado pelo Laravel com as configurações do Docker:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

### 3. Subir o ambiente

```bash
# Subir containers principais
make up

# Ou com ferramentas extras (phpMyAdmin + Mailpit)
make up-tools
```

### 4. Instalar dependências e configurar

```bash
make install
```

Acesse: **http://localhost**

## 📋 Comandos Úteis

```bash
make help              # Ver todos os comandos disponíveis

# Docker
make up                # Subir containers
make down              # Parar containers
make build             # Rebuild dos containers
make logs s=app        # Ver logs (app, nginx, mysql, redis)
make ps                # Ver containers rodando

# Acesso
make shell             # Acessar shell do container PHP
make mysql             # Acessar MySQL CLI
make redis             # Acessar Redis CLI

# Laravel
make artisan c="migrate"              # Rodar Artisan
make composer c="require package"     # Rodar Composer
make npm c="run dev"                  # Rodar npm

# Testes
make test              # Rodar testes
make test-coverage     # Testes com cobertura
make lint              # Code style (Pint)

# Manutenção
make fresh             # Reset do banco + seed
make cache             # Limpar caches
make optimize          # Otimizar aplicação
make permissions       # Corrigir permissões
make destroy           # Destruir tudo (CUIDADO!)
```

## 🗂 Estrutura Docker

```
.
├── docker/
│   ├── php/
│   │   ├── Dockerfile        # Imagem PHP com extensões
│   │   ├── php.ini           # Configurações PHP customizadas
│   │   └── entrypoint.sh     # Script de inicialização
│   └── nginx/
│       └── default.conf      # Configuração do Nginx
├── docker-compose.yml        # Orquestração dos serviços
├── .env.docker               # Variáveis do Docker
├── .dockerignore             # Arquivos ignorados no build
└── Makefile                  # Atalhos de comandos
```

## ⚙️ Personalização

### Alterar portas

Edite o arquivo `.env.docker` (ou `.env` na raiz):

```env
APP_PORT=8000       # Porta do Nginx (default: 80)
DB_PORT=33060       # Porta do MySQL (default: 3306)
REDIS_PORT=63790    # Porta do Redis (default: 6379)
PMA_PORT=8081       # Porta do phpMyAdmin (default: 8080)
```

### Usar PostgreSQL em vez de MySQL

Basta trocar o serviço `mysql` no `docker-compose.yml` por:

```yaml
postgres:
  image: postgres:17-alpine
  environment:
    POSTGRES_DB: ${DB_DATABASE:-laravel}
    POSTGRES_USER: ${DB_USERNAME:-laravel}
    POSTGRES_PASSWORD: ${DB_PASSWORD:-secret}
  volumes:
    - postgres_data:/var/lib/postgresql/data
```

O driver `pdo_pgsql` já está instalado no Dockerfile.

## 📧 Mailpit

Quando o profile `tools` estiver ativo, o Mailpit captura todos os e-mails enviados pela aplicação.

- **Dashboard:** http://localhost:8025
- **SMTP:** mailpit:1025

## 🔒 Importante

Este setup é **exclusivo para desenvolvimento**. Não use em produção sem as devidas adaptações de segurança.
