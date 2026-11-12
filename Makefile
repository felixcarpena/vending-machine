IMAGE = vending-machine-dev
TTY = $(shell [ -t 0 ] && echo -t)
RUN = docker run --rm -i $(TTY) \
	--user $(shell id -u):$(shell id -g) \
	--volume $(CURDIR):/app \
	--env COMPOSER_HOME=/tmp/composer \
	--env PHP_IDE_CONFIG=serverName=vending-machine \
	--add-host host.docker.internal:host-gateway \
	$(if $(DEBUG),--env XDEBUG_TRIGGER=1) \
	$(IMAGE)

.PHONY: build install shell acceptance spec-status

build:
	docker build --target dev --tag $(IMAGE) .

install: build
	$(RUN) composer install

shell:
	$(RUN) bash

acceptance:
	$(RUN) vendor/bin/behat

spec-status:
	$(RUN) vendor/bin/behat --profile=wip --dry-run --no-snippets
