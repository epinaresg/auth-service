.PHONY: up down build shell composer artisan npm migrate seed test logs

up:
	docker compose up -d --build

down:
	docker compose down -v

build:
	docker compose build --no-cache

shell:
	docker compose exec app bash

composer:
	docker compose run --rm app composer $(cmd)

artisan:
	docker compose exec app php artisan $(cmd)

npm:
	docker compose run --rm node npm $(cmd)

migrate:
	docker compose exec app php artisan migrate --force

seed:
	docker compose exec app php artisan db:seed --force

logs:
	docker compose logs -f

test:
	docker compose exec app ./vendor/bin/pest --colors
