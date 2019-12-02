-include .env

help:
	@echo ""
	@echo "usage: make COMMAND"
	@echo ""
	@echo "Commands:"
	@echo "  setup               Setup default setting for simple run"
	@echo "  up                  Run all containers in detached mode"
	@echo "  stop                Stop all containers"
	@echo "  restart             Restart all containers"
	@echo "  rm                  Stop and remove all containers"
	@echo "  logs                Follow logs"
	@echo "  dump                Dump db data"
	@echo "  restore-dump        Restore db data"
	@echo "  tests               Run tests"
	@echo "  coverage            Run code coverage"

up: setup build
	@docker-compose up -d

build: setup
	@docker-compose build

stop:
	@docker-compose stop

restart:
	@docker-compose restart

rm:
	@docker-compose rm --stop --force

logs:
	@docker-compose logs -f -t --tail=1000

setup:
	@cp -n .env.example .env
	@cp -n docker-compose.override.example.yml docker-compose.override.yml

dump:
	@docker-compose up -d db
	@docker-compose exec db bash /wait.sh
	@docker-compose exec db pg_dump --schema public --create --dbname=$(POSTGRES_DB) --username=$(POSTGRES_USER) > ./data/dump.sql
	@echo 'file available in ./dump.sql'

restore-dump:
	@docker-compose up -d db
	@docker-compose exec db bash /wait.sh
	@docker exec -i $(CONTAINER_PREFIX)_db psql --dbname=postgres --username=$(POSTGRES_USER) < ./data/dump.sql

tests: setup
	@cd ./tests && make run

coverage: setup
	@cd ./tests && make coverage
