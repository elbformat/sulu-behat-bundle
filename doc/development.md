For local development you can build a docker image and use it.
```bash
docker build . -f docker/Dockerfile.php -t sulu-behat-php
docker run -it -v $(pwd):/var/www -w /var/www sulu-behat-php sh
composer install
```

Enable xdebug inside the container
```bash
export XDEBUG_CONFIG="client_host=172.17.0.1 idekey=PHPSTORM"
export XDEBUG_MODE="debug"
```
