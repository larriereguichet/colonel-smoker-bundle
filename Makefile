composer@install: composer.lock

composer.lock:
	composer install


php-cs-fixer:
	php bin/php-cs-fixer fix

phpunit:
	bin/phpunit
