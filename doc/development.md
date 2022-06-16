For local development you can use docker-compose.
```bash
docker-compose run php sh
composer install
bin/console sulu:build dev -n
```

Enable xdebug inside the container
```bash
export XDEBUG_CONFIG="client_host=172.17.0.1 idekey=PHPSTORM"
export XDEBUG_MODE="debug"
```

Run tests
```bash
vendor/bin/phpunit
vendor/bin/psalm
vendor/bin/php-cs-fixer
phpdbg -qrr -d memory_limit=-1 vendor/bin/behat
phpdbg -qrr -d memory_limit=-1 vendor/bin/behat --profile admin
```