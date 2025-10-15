.PHONY: test test-filter migrate fresh seed shell logs tinker lint format phpstan phpstan-baseline db redis clean install dev build

# Testing
test:
	docker compose exec app php -d xdebug.mode=off artisan test

test-filter:
	docker compose exec -T app php artisan test --filter=$(FILTER)

# Database
migrate:
	docker compose exec -T app php artisan migrate

fresh:
	docker compose exec -T app php artisan migrate:fresh --seed

seed:
	docker compose exec -T app php artisan db:seed

# Development
shell:
	docker compose exec app sh

logs:
	docker compose logs app --tail=50 --follow

tinker:
	docker compose exec app php artisan tinker

# Code Quality
lint:
	docker compose exec -T app ./vendor/bin/pint --test

format:
	docker compose exec -T app ./vendor/bin/pint

phpstan:
	docker compose exec -T app ./vendor/bin/phpstan analyse --memory-limit=1G

phpstan-baseline:
	docker compose exec -T app ./vendor/bin/phpstan analyse --memory-limit=1G --generate-baseline

# Database Access
db:
	docker compose exec postgres psql -U beatwager beatwager

redis:
	docker compose exec redis redis-cli

# Utilities
clean:
	docker compose exec -T app php artisan optimize:clear

install:
	docker compose exec -T app composer install
	docker compose exec -T app npm install

# Frontend
dev:
	docker compose exec app npm run dev

build:
	docker compose exec -T app npm run build
