composer@install: composer.lock php-cs-fixer tests test@phpunit

composer.lock:
	composer install

php-cs-fixer:
	php bin/php-cs-fixer fix

tests: test@phpunit

test@phpunit: php-cs-fixer
	bin/phpunit
	@echo "Results file generated file://$(shell pwd)/var/phpunit/build/coverage/index.html"
