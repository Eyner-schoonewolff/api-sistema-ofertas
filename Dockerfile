# 1. Usamos la imagen oficial de PHP 8.4
FROM php:8.4-cli

# 2. Instalamos dependencias del sistema necesarias para Composer y extensiones
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql

# 3. Instalamos Composer formalmente desde su imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Definimos el directorio de trabajo
WORKDIR /app

# 5. Copiamos los archivos de dependencias primero (optimiza la caché de Docker)
COPY composer.json composer.lock* ./

# 6. Instalamos las dependencias de PHP
RUN composer install --no-dev --optimize-autoloader

# 7. Copiamos el resto de los archivos del proyecto
COPY . .

# 8. Comando para iniciar la API
CMD php -S 0.0.0.0:$PORT -t public
