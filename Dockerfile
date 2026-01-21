# Usamos la imagen oficial de PHP 8.4
FROM php:8.4-cli

# Instalamos extensiones comunes (opcional, por si usas bases de datos)
RUN docker-php-ext-install pdo pdo_mysql

# Definimos el directorio de trabajo dentro del contenedor
WORKDIR /app

# Copiamos todos los archivos de tu proyecto al contenedor
COPY . .

# Exponemos el puerto que Render nos asigne
# PHP debe escuchar en 0.0.0.0 para ser accesible externamente
CMD php -S 0.0.0.0:$PORT -t public
