COMPOSE=docker compose
PHP=docker exec -it diploma-hr-php
CONSOLE=$(PHP) php bin/console

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

install:
	@${PHP} npm install && ${PHP} composer install

watch:
	@${PHP} npm run watch

fix_src:
	vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix src

-include local.mk
