IMAGE = vending-machine-dev
TTY = $(shell [ -t 0 ] && echo -t)
DOCKER_RUN = docker run --rm -i $(TTY) \
	--user $(shell id -u):$(shell id -g) \
	--volume $(CURDIR):/app \
	--env COMPOSER_HOME=/tmp/composer \
	--env PHP_IDE_CONFIG=serverName=vending-machine \
	--add-host host.docker.internal:host-gateway \
	$(if $(DEBUG),--env XDEBUG_TRIGGER=1)
RUN = $(DOCKER_RUN) $(IMAGE)
RUN_WITH_COVERAGE = $(DOCKER_RUN) --env XDEBUG_MODE=coverage $(IMAGE)

.PHONY: build install run shell test analyse lint fix acceptance spec-status clear-cache coverage mutation

build:
	docker build --target dev --tag $(IMAGE) .

install: build
	$(RUN) composer install

run: clear-cache
	$(RUN) bin/vending-machine

shell:
	$(RUN) bash

test:
	$(RUN) vendor/bin/phpunit --testdox

analyse:
	$(RUN) vendor/bin/phpstan analyse --no-progress

lint:
	$(RUN) vendor/bin/php-cs-fixer check --diff

fix:
	$(RUN) vendor/bin/php-cs-fixer fix

acceptance: clear-cache
	$(RUN) vendor/bin/behat

spec-status:
	$(RUN) vendor/bin/behat --profile=wip --dry-run --no-snippets

coverage:
	$(RUN_WITH_COVERAGE) vendor/bin/phpunit --coverage-text --only-summary-for-coverage-text

mutation:
	$(RUN_WITH_COVERAGE) vendor/bin/infection --threads=max --show-mutations=0

clear-cache:
	rm -rf var/cache
