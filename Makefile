COMPOSE=docker compose
PHP=$(COMPOSE) exec php
CONSOLE=$(PHP) bin/console
COMPOSER=$(PHP) composer

up:
	@${COMPOSE} up -d

down:
	@${COMPOSE} down

clear:
	@${CONSOLE} cache:clear

entity:
	@${CONSOLE} make:entity

controller:
	@${CONSOLE} make:controller

require:
	@${COMPOSER} require $2

npm:
	@${COMPOSE} run node npm install

watch:
	@${COMPOSE} run node npm run watch

encore_dev:
	@${COMPOSE} run node yarn encore dev --watch

encore_prod:
	@${COMPOSE} run node yarn encore production

phpunit:
	@${PHP} bin/phpunit --testdox

fix_src:
	vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix src

-include local.mk
