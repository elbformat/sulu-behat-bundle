ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))
DOCKER_RUN ?= docker compose run --rm php

.PHONY: php-cs-fixer
php-cs-fixer:
	docker run -v "$(ROOT_DIR):/code" --rm ghcr.io/php-cs-fixer/php-cs-fixer:3.95-php8.3 fix --diff

.PHONY: phpstan
phpstan:
	$(DOCKER_RUN) vendor/bin/phpstan --memory-limit=-1

.PHONY: phpunit
phpunit:
	$(DOCKER_RUN) vendor/bin/phpunit

.PHONY: shell
shell:
	$(DOCKER_RUN) sh

.PHONY: behat
behat:
	docker compose up -d
	docker compose run -it --rm php php -d memory_limit=1G vendor/bin/behat
	docker compose down

.PHONY: docker
docker:
	docker build -t ghcr.io/elbformat/sulu-behat-bundle/php:8.3 . -f docker/Dockerfile.8.3
	docker build -t ghcr.io/elbformat/sulu-behat-bundle/php:8.4 . -f docker/Dockerfile.8.4
	docker build -t ghcr.io/elbformat/sulu-behat-bundle/php:8.5 . -f docker/Dockerfile.8.5

.PHONY: clean
clean:
	rm -f .php-cs-fixer.cache .phpunit.result.cache composer.lock behat.yml.dist
	git checkout -- composer.json
