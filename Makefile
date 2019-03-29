composer@install: composer.lock

composer.lock:
	composer install


current_dir = $(shell pwd)

php-cs-fixer:
	php bin/php-cs-fixer fix

test@phpunit:
	bin/phpunit
	@echo "Results file generated file://$(current_dir)/var/phpunit/build/coverage/index.html"
