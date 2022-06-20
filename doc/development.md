For local development you can use docker-compose.
```bash
docker-compose run php sh
composer install
bin/console sulu:build dev -n
bin/console sulu:document:init
```

Enable xdebug inside the container
```bash
pecl install xdebug-3.1.4
docker-php-ext-enable xdebug
export XDEBUG_CONFIG="client_host=172.17.0.1 idekey=PHPSTORM"
export XDEBUG_MODE="debug"
```

Run tests
```bash
vendor/bin/phpunit
vendor/bin/psalm
vendor/bin/php-cs-fixer fix --diff src
vendor/bin/php-cs-fixer fix --diff tests
phpdbg -qrr -d memory_limit=-1 vendor/bin/behat
phpdbg -qrr -d memory_limit=-1 vendor/bin/behat --profile admin
```