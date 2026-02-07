.PHONY: help install test coverage phpstan cs-check cs-fix qa up down restart docker-build shell docker-shell docker-test docker-qa docker-analyze analyze clean

# Default target
help:
	@echo "BC Check - Backward Compatibility Checker for PHP"
	@echo ""
	@echo "Available targets:"
	@echo "  install      Install composer dependencies"
	@echo "  test         Run PHPUnit tests"
	@echo "  coverage     Generate HTML coverage report"
	@echo "  phpstan      Run PHPStan static analysis"
	@echo "  cs-check     Check code style (dry-run)"
	@echo "  cs-fix       Fix code style"
	@echo "  qa           Run all quality checks (phpstan, cs-check, test)"
	@echo ""
	@echo "Analysis targets:"
	@echo "  analyze REPO=<path> FROM=<commit> TO=<commit>  Run BC check locally"
	@echo "  docker-analyze REPO=<path> FROM=<commit> TO=<commit>  Run BC check in Docker"
	@echo ""
	@echo "Docker targets:"
	@echo "  up           Start containers in background"
	@echo "  down         Stop and remove containers"
	@echo "  restart      Restart containers"
	@echo "  docker-build Build Docker image"
	@echo "  shell        Open shell in Docker container"
	@echo "  docker-shell Open shell in Docker container"
	@echo "  docker-test  Run tests in Docker container"
	@echo "  docker-qa    Run all QA checks in Docker container"
	@echo ""
	@echo "Other:"
	@echo "  clean        Remove generated files"

# Local development
install:
	composer install

test:
	vendor/bin/phpunit

coverage:
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html coverage

phpstan:
	vendor/bin/phpstan analyse

cs-check:
	vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix:
	vendor/bin/php-cs-fixer fix

qa: phpstan cs-check test

# Analysis targets
# Usage: make analyze REPO=/path/to/repo FROM=v1.0.0 TO=v2.0.0
analyze:
ifndef REPO
	$(error REPO is required. Usage: make analyze REPO=/path/to/repo FROM=v1.0.0 TO=v2.0.0)
endif
ifndef FROM
	$(error FROM is required. Usage: make analyze REPO=/path/to/repo FROM=v1.0.0 TO=v2.0.0)
endif
ifndef TO
	$(error TO is required. Usage: make analyze REPO=/path/to/repo FROM=v1.0.0 TO=v2.0.0)
endif
	vendor/bin/bc-check check $(REPO) $(FROM) $(TO) $(if $(CONFIG),--config=$(CONFIG),) $(if $(FORMAT),--format=$(FORMAT),)

# Docker targets
up:
	docker-compose up -d

down:
	docker-compose down

restart:
	docker-compose restart

docker-build:
	docker-compose build

shell:
	docker-compose run --rm php bash

docker-shell:
	docker-compose run --rm php bash

docker-test:
	docker-compose run --rm php vendor/bin/phpunit

docker-phpstan:
	docker-compose run --rm php vendor/bin/phpstan analyse

docker-cs-check:
	docker-compose run --rm php vendor/bin/php-cs-fixer fix --dry-run --diff

docker-cs-fix:
	docker-compose run --rm php vendor/bin/php-cs-fixer fix

docker-qa: docker-phpstan docker-cs-check docker-test

# Run BC check in Docker against an external repository
# Usage: make docker-analyze REPO=/path/to/repo FROM=v1.0.0 TO=v2.0.0
docker-analyze:
ifndef REPO
	$(error REPO is required. Usage: make docker-analyze REPO=/path/to/repo FROM=v1.0.0 TO=v2.0.0)
endif
ifndef FROM
	$(error FROM is required. Usage: make docker-analyze REPO=/path/to/repo FROM=v1.0.0 TO=v2.0.0)
endif
ifndef TO
	$(error TO is required. Usage: make docker-analyze REPO=/path/to/repo FROM=v1.0.0 TO=v2.0.0)
endif
	docker-compose run --rm -v $(REPO):/target:ro php bin/bc-check check /target $(FROM) $(TO) $(if $(CONFIG),--config=$(CONFIG),) $(if $(FORMAT),--format=$(FORMAT),)

# Cleanup
clean:
	rm -rf vendor/
	rm -rf .phpunit.cache/
	rm -rf .php-cs-fixer.cache
	rm -f bc-check.phar
