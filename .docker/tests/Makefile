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
	@echo "  run                 Run codeception testing framework"

up: setup build
	@docker-compose up -d
	@docker-compose exec web bash /wait-for.sh localhost:80 -t 0 -- echo "Webserver started"

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
	@ln -sf $(PWD)/../.env $(PWD)/.env
	@cp -n docker-compose.override.example.yml docker-compose.override.yml

run: up
	@docker-compose exec php ./vendor/bin/phinx migrate
	@docker-compose exec php ./vendor/bin/codecept run

coverage: up
	@docker-compose exec php ./vendor/bin/phinx migrate
	@docker-compose exec php ./vendor/bin/codecept run --coverage --coverage-xml
	@if [ ! -z "${TRAVIS}" ]; then \
	 	docker-compose exec php mkdir -p ./build/logs && \
		docker-compose exec php ./vendor/bin/php-coveralls -v --coverage_clover=./tests/_output/coverage.xml; \
	fi
