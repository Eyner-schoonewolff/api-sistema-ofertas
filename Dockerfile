FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock* ./

RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

COPY . .

CMD php -S 0.0.0.0:$PORT -t public
