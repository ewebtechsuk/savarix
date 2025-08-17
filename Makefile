.PHONY: init dev test test-parallel fresh seed optimize reset-db stan ci-local help

help: ## Show available commands
	@echo "Available commands:"
	@echo "  init          - Run initial setup with database wait and seed"
	@echo "  dev           - Start development server"
	@echo "  test          - Run tests"
	@echo "  test-parallel - Run tests in parallel"
	@echo "  fresh         - Fresh migration with seed"
	@echo "  seed          - Run database seeder"
	@echo "  optimize      - Run setup optimization"
	@echo "  reset-db      - Drop & recreate schema without seeding"
	@echo "  stan          - Run PHPStan analysis"
	@echo "  ci-local      - Run local CI checks (Pint, PHPStan, tests)"

init:
	./setup.sh --db-wait --seed

dev:
	php artisan serve --host=0.0.0.0 --port=8000

test:
	php artisan test

test-parallel:
	php artisan test --parallel

fresh:
	php artisan migrate:fresh --seed --force

seed:
	php artisan db:seed --force

optimize:
	./setup.sh --skip-migrate --optimize

reset-db: ## Drops & recreates schema without seeding
	php artisan migrate:fresh --force

stan: ## Run PHPStan analysis
	vendor/bin/phpstan analyse --memory-limit=512M

ci-local: ## Run local CI checks
	vendor/bin/pint --test
	vendor/bin/phpstan analyse --memory-limit=512M
	php artisan test