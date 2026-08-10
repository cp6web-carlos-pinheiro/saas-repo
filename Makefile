COMPOSE_FILE := .infrastructure/docker/.docker/compose.yaml
COMPOSE := docker compose -f $(COMPOSE_FILE)
COMPOSE_CLASSIC := DOCKER_BUILDKIT=0 COMPOSE_BAKE=false docker compose -f $(COMPOSE_FILE)
WEB := web

.PHONY: help config build prepare up down stop restart ps logs shell composer artisan npm test init

help:
	@printf "Beyond MRP development commands\n\n"
	@printf "  make init       Build, install, start and migrate the local stack\n"
	@printf "  make config     Validate the Docker Compose configuration\n"
	@printf "  make build      Build the application images\n"
	@printf "  make up         Start all services\n"
	@printf "  make down       Stop and remove containers\n"
	@printf "  make ps         Show service status\n"
	@printf "  make logs       Follow service logs\n"
	@printf "  make shell      Open a shell in the web container\n"
	@printf "  make artisan CMD=\"migrate\"\n"
	@printf "  make composer CMD=\"install\"\n"
	@printf "  make npm CMD=\"run build\"\n"
	@printf "  make test       Run tests with SQLite in memory\n"

config:
	$(COMPOSE) config --quiet

build:
	$(COMPOSE_CLASSIC) build web worker

prepare:
	$(COMPOSE) run --rm $(WEB) sh -lc 'mkdir -p bootstrap/cache storage/app/private storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views storage/logs && chmod -R 0777 storage bootstrap/cache'

up:
	$(COMPOSE) up -d
	@port="$$( $(COMPOSE) port $(WEB) 8080 | sed -n '1s/.*://p' )"; \
	printf "\nBeyond MRP esta rodando em:\n"; \
	printf "  Projeto: http://localhost:%s\n" "$$port"; \
	printf "  Admin:   http://localhost:%s/global-admin/login\n" "$$port"; \
	printf "  Cliente: http://localhost:%s/login\n\n" "$$port"

down:
	$(COMPOSE) down

stop:
	$(COMPOSE) stop

restart:
	$(COMPOSE) restart

ps:
	$(COMPOSE) ps

logs:
	$(COMPOSE) logs -f

shell:
	$(COMPOSE) run --rm $(WEB) sh

composer: prepare
	$(COMPOSE) run --rm $(WEB) composer $(CMD)

artisan: prepare
	$(COMPOSE) run --rm $(WEB) php artisan $(CMD)

npm:
	$(COMPOSE) run --rm $(WEB) npm $(CMD)

test: prepare
	$(COMPOSE) run --rm -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: -e CACHE_STORE=array -e SESSION_DRIVER=array -e QUEUE_CONNECTION=sync $(WEB) php artisan test --env=testing

init: build prepare
	$(COMPOSE) run --rm $(WEB) composer install --no-interaction --prefer-dist
	$(COMPOSE) run --rm $(WEB) npm ci
	$(COMPOSE) up -d
	$(COMPOSE) exec $(WEB) php artisan migrate --seed --force
