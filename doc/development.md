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
