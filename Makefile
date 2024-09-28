.DEFAULT_GOAL := help

# Detect whether to use docker-compose or docker compose
DOCKER_COMPOSE = $(shell if command -v docker-compose > /dev/null 2>&1; then echo "docker-compose"; else echo "docker compose"; fi)

help:
	@echo "Please choose what you want to do: \n" \
	" make dup: start docker container \n" \
	" make ddw: stop docker container \n" \
	" make drs: restart docker container \n" \
	" make dci: composer install inside container \n" \
	" make dcu: composer update inside container \n" \
	" make mysql: go into the mysql container \n" \
	" make access-php: go into the php container \n" \
	" make test: run tests with code coverage \n" \
	" make mig: run migrations with seed \n"

build:
	export COMPOSE_FILE=docker-compose.yml; cp .env.example .env; $(DOCKER_COMPOSE) --env-file .env up -d --build

dup:
	export COMPOSE_FILE=docker-compose.yml; cp .env.example .env; $(DOCKER_COMPOSE) --env-file .env up -d

ddw:
	export COMPOSE_FILE=docker-compose.yml; $(DOCKER_COMPOSE) down --volumes

drs:
	export COMPOSE_FILE=docker-compose.yml; $(DOCKER_COMPOSE) down --volumes && $(DOCKER_COMPOSE) up -d

dci: check_containers
	docker exec -it php composer install && sudo chown -R $(USER):$(shell id -g) vendor/

dcu: check_containers
	docker exec -it php composer update && sudo chown -R $(USER):$(shell id -g) vendor/

mysql: check_containers
	docker exec -it database bash

php: check_containers
	docker exec -it php bash

test: check_containers
	docker exec -it php bash -c "touch database/database.sqlite"
	docker exec -it php bash -c "php artisan migrate:fresh --env=testing"
	docker exec -it php bash -c "export XDEBUG_MODE=coverage && php vendor/bin/phpunit --coverage-html coverage"
	@echo "Tests and code coverage generated in the coverage directory."

lvtest: check_containers
	docker exec -it php bash -c "php artisan test"

mig: check_containers
	docker exec -it php bash -c "php artisan migrate:fresh --seed"

check_containers:
	@docker inspect -f '{{.State.Running}}' php 2>/dev/null | grep true > /dev/null || (echo "Starting containers..." && make dup)