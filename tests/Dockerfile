FROM composer:2 AS composer
FROM php:8.3-cli-bookworm
WORKDIR /app
ENV COMPOSER_ALLOW_SUPERUSER=1 XDEBUG_MODE=off
RUN apt-get update && apt-get install -y --no-install-recommends libzip-dev unzip \
    && docker-php-ext-install -j2 zip \
    && pecl install xdebug-3.5.3 && docker-php-ext-enable xdebug \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY . .
RUN composer install --no-interaction --prefer-dist --no-progress
CMD ["sh", "tests/run-container-tests.sh"]
