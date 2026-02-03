FROM hgiesenow/php:8.2
LABEL org.opencontainers.image.source="https://github.com/elbformat/sulu-behat-bundle"

# ext-intl
RUN apk add --no-cache icu icu-data-full && \
    apk add --no-cache --virtual .build-deps icu-dev && \
    docker-php-ext-install intl  && \
    apk del .build-deps && \
    rm -rf /tmp/*

# ext-gd
RUN apk add --no-cache libpng libjpeg-turbo freetype && \
    apk add --no-cache --virtual .build-deps libpng-dev libjpeg-turbo-dev freetype-dev && \
    docker-php-ext-configure gd && \
    docker-php-ext-install gd && \
    apk del .build-deps && \
    rm -rf /tmp/*

# xdebug
RUN apk add --no-cache --virtual .build-deps autoconf g++ make linux-headers && \
pecl install xdebug-3.2.1 && \
docker-php-ext-enable xdebug && \
apk del .build-deps && \
rm -rf /tmp/*

# Database connection (for integration tests)
RUN docker-php-ext-install pdo pdo_mysql

# For codecov upload inside circleci
RUN apk add gpg gpg-agent gpgv

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_MEMORY_LIMIT=-1

COPY php.ini /usr/local/etc/php/conf.d/

WORKDIR /var/www