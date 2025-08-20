FROM php:8.3-fpm-alpine

# Extensiones necesarias
RUN apk add --no-cache bash git libpq-dev $PHPIZE_DEPS \
  && docker-php-ext-install pdo pdo_pgsql \
  && pecl install redis \
  && docker-php-ext-enable redis

WORKDIR /var/www/html

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiamos la app (si usás volumen en docker-compose, esto no es estrictamente necesario,
# pero no molesta y facilita builds en CI)
COPY . .
