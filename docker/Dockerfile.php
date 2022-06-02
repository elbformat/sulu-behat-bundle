FROM php:7.4-alpine
RUN docker-php-ext-install pdo pdo_mysql

#I know this is bad practice, but it's for testing only
RUN apk add --no-cache autoconf make g++
RUN pecl install xdebug-3.1.4
RUN docker-php-ext-enable xdebug

RUN apk add --no-cache zlib-dev libpng libpng-dev libjpeg-turbo libjpeg-turbo-dev libwebp libwebp-dev freetype freetype-dev
RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype
RUN docker-php-ext-install gd

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_MEMORY_LIMIT=-1
WORKDIR /var/www
RUN echo "error_log = /var/log/php.log" >> /usr/local/etc/php/conf.d/php.ini