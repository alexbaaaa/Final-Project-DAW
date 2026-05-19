FROM php:8.4-cli-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    zip \
    unzip \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

EXPOSE 8000

CMD ["sh", "-c", "if [ -f artisan ]; then php artisan serve --host=0.0.0.0 --port=8000; else echo 'Backend Laravel pendiente de crear en ./backend'; tail -f /dev/null; fi"]
