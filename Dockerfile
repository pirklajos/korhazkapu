FROM composer:2.8 AS composer

FROM php:8.3-cli-bookworm AS php-base

RUN apt-get update \
    && apt-get install -y --no-install-recommends git libicu-dev libpq-dev libxml2-dev unzip \
    && docker-php-ext-install intl opcache pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
WORKDIR /app

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-interaction --no-scripts --prefer-dist

COPY . .
RUN composer dump-autoload --classmap-authoritative \
    && php bin/console cache:clear --env=prod \
    && php bin/console asset-map:compile

FROM php-base AS development

ENV APP_ENV=dev
CMD ["sh", "-c", "composer install --no-interaction && php -S 0.0.0.0:8000 -t public public/index.php"]

FROM php-base AS production

ENV APP_ENV=prod
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]
