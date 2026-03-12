# ─────────────────────────────────────────────────────────────────────
# Auth Service — Makefile
# Shortcuts for daily Docker Compose operations
#
# Usage:  make <target>
# ─────────────────────────────────────────────────────────────────────

.PHONY: help build up down restart logs shell \
        setup migrate seed test coverage \
        keys lint stan clean

# ── Default target ────────────────────────────────────────────────────
help:
	@echo ""
	@echo "  Auth Service — available commands"
	@echo ""
	@echo "  Setup"
	@echo "    make setup      First-time setup: build + copy .env + key:generate + keys + migrate + seed"
	@echo ""
	@echo "  Daily"
	@echo "    make up         Start all containers (detached)"
	@echo "    make down       Stop and remove containers (data volumes preserved)"
	@echo "    make restart    Restart the app container only"
	@echo "    make logs       Tail app logs"
	@echo "    make shell      Open a bash shell inside the app container"
	@echo ""
	@echo "  Database"
	@echo "    make migrate    Run pending migrations"
	@echo "    make seed       Run all seeders"
	@echo "    make fresh      Drop all tables + migrate + seed (DEV ONLY)"
	@echo ""
	@echo "  Testing"
	@echo "    make test       Run full Pest test suite with coverage"
	@echo "    make test-fast  Run test suite without coverage (faster)"
	@echo ""
	@echo "  Code quality"
	@echo "    make lint       Laravel Pint (PSR-12 auto-fix)"
	@echo "    make stan       PHPStan level 8"
	@echo ""
	@echo "  Keys"
	@echo "    make keys       Generate RSA key pair (storage/keys/)"
	@echo "    make keys-test  Generate RSA test key pair (tests/keys/)"
	@echo ""
	@echo "  Cleanup"
	@echo "    make clean      Remove containers + volumes (DESTROYS DATA)"
	@echo ""

# ── First-time setup ──────────────────────────────────────────────────
setup:
	@echo "→ Copying .env.docker to .env ..."
	cp -n .env.docker .env || echo "  .env already exists — skipping"
	@echo "→ Building images ..."
	docker compose build
	@echo "→ Starting containers ..."
	docker compose up -d
	@echo "→ Generating application key ..."
	docker compose exec app php artisan key:generate
	@echo "→ Generating RSA keys ..."
	docker compose exec app php artisan auth:generate-keys
	@echo "→ Running migrations ..."
	docker compose exec app php artisan migrate --force
	@echo "→ Running seeders ..."
	docker compose exec app php artisan db:seed --force
	@echo ""
	@echo "✓ Auth Service ready at http://localhost:8000"
	@echo "✓ JWKS endpoint: http://localhost:8000/api/v1/auth/jwks"

# ── Build ─────────────────────────────────────────────────────────────
build:
	docker compose build

# ── Lifecycle ─────────────────────────────────────────────────────────
up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart app

logs:
	docker compose logs -f app

shell:
	docker compose exec app bash

# ── Database ──────────────────────────────────────────────────────────
migrate:
	docker compose exec app php artisan migrate --force

seed:
	docker compose exec app php artisan db:seed --force

fresh:
	@echo "⚠ This will DROP ALL TABLES in pharmacy_auth. Press Ctrl+C to cancel."
	@sleep 3
	docker compose exec app php artisan migrate:fresh --seed --force

# ── Testing ───────────────────────────────────────────────────────────
test:
	docker compose \
		-f docker-compose.yml \
		-f docker-compose.testing.yml \
		run --rm test

test-fast:
	docker compose \
		-f docker-compose.yml \
		-f docker-compose.testing.yml \
		run --rm test \
		sh -c "php artisan migrate --env=testing --force && ./vendor/bin/pest --colors=always"

# ── Code quality ──────────────────────────────────────────────────────
lint:
	docker compose exec app ./vendor/bin/pint

stan:
	docker compose exec app ./vendor/bin/phpstan analyse --level=8 src/

# ── RSA Keys ─────────────────────────────────────────────────────────
keys:
	docker compose exec app php artisan auth:generate-keys

keys-test:
	docker compose exec app php artisan auth:generate-test-keys

# ── Cleanup ───────────────────────────────────────────────────────────
clean:
	@echo "⚠ This will DESTROY all containers and volumes (including DB data)."
	@echo "   Press Ctrl+C within 5 seconds to cancel."
	@sleep 5
	docker compose down -v --remove-orphans
	@echo "✓ Clean complete."
