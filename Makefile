composer@install: composer.lock

composer.lock:
	composer install


php-cs-fixer:
	php bin/php-cs-fixer fix

test@phpunit:
	bin/phpunit
	@echo "Results file generated file://$(shell pwd)/var/phpunit/build/coverage/index.html"
