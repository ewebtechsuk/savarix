.PHONY: init dev test test-parallel fresh seed optimize reset-db

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