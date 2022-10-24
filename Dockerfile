FROM composer:2 as builder

COPY composer.json /app/

RUN composer install \
  --ignore-platform-reqs \
  --no-ansi \
  --no-autoloader \
  --no-interaction \
  --no-scripts

COPY . /app/

RUN composer dump-autoload --optimize --classmap-authoritative

FROM php:7.4-cli as base

RUN  apt-get update \
    && apt-get install -y --no-install-recommends build-essential git python \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install pdo_mysql

# Add php extensions configuration
COPY docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Cleanup
RUN rm -rf /var/lib/apt/lists/*
RUN rm -rf /tmp/pear/

# Setup working directory
WORKDIR /app

COPY --from=builder /app /app
