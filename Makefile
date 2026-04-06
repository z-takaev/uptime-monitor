up:
	docker compose up -d

down:
	docker compose down

bash:
	docker compose exec app bash

migrate:
	docker compose exec app php artisan migrate

test:
	docker compose exec app php artisan test

logs:
	docker compose logs -f