.PHONY: help up down build restart logs shell mysql redis composer artisan npm fresh install test lint

# Colors
GREEN  := \033[0;32m
YELLOW := \033[0;33m
RESET  := \033[0m

help: ## Show this help
	@echo ""
	@echo "$(GREEN)Laravel Docker Commands$(RESET)"
	@echo "========================="
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(YELLOW)%-15s$(RESET) %s\n", $$1, $$2}'
	@echo ""

# ─── Docker ──────────────────────────────────────────────

up: ## Start all containers
	docker compose up -d

up-tools: ## Start all containers including tools (phpMyAdmin, Mailpit)
	docker compose --profile tools up -d

down: ## Stop all containers
	docker compose --profile tools down

build: ## Build/rebuild containers
	docker compose build --no-cache

restart: ## Restart all containers
	docker compose restart

logs: ## View container logs (use: make logs s=app)
	docker compose logs -f $(s)

ps: ## Show running containers
	docker compose ps

# ─── Shell Access ────────────────────────────────────────

shell: ## Access app container shell
	docker compose exec app bash

mysql: ## Access MySQL CLI
	docker compose exec mysql mysql -u root -proot laravel

redis: ## Access Redis CLI
	docker compose exec redis redis-cli

# ─── Laravel ─────────────────────────────────────────────

composer: ## Run Composer command (use: make composer c="require package/name")
	docker compose exec app composer $(c)

artisan: ## Run Artisan command (use: make artisan c="migrate")
	docker compose exec app php artisan $(c)

npm: ## Run npm command (use: make npm c="run dev")
	docker compose exec app npm $(c)

# ─── Project Setup ───────────────────────────────────────

install: ## First time setup: install dependencies, generate key, run migrations
	docker compose exec app composer install
	docker compose exec app cp -n .env.example .env || true
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate --seed
	docker compose exec app npm install
	@echo "$(GREEN)✅ Project installed successfully!$(RESET)"

fresh: ## Reset database and seed
	docker compose exec app php artisan migrate:fresh --seed

# ─── Testing ─────────────────────────────────────────────

test: ## Run tests
	docker compose exec app php artisan test

test-coverage: ## Run tests with coverage
	docker compose exec app php artisan test --coverage

lint: ## Run Pint (code style fixer)
	docker compose exec app ./vendor/bin/pint

# ─── Maintenance ─────────────────────────────────────────

cache: ## Clear all caches
	docker compose exec app php artisan optimize:clear

optimize: ## Optimize the application
	docker compose exec app php artisan optimize

permissions: ## Fix storage permissions
	docker compose exec app chmod -R 775 storage bootstrap/cache

destroy: ## Remove all containers, volumes and networks (CAUTION: destroys data)
	docker compose --profile tools down -v --remove-orphans
	@echo "$(YELLOW)⚠️  All containers and volumes have been destroyed$(RESET)"
