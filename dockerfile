# Dockerfile
FROM php:8.0-apache

# Instalamos dependencias necesarias y la extensión mysqli
RUN apt-get update && \
    apt-get install -y libmariadb-dev && \
    docker-php-ext-install mysqli pdo pdo_mysql

# Copiar el contenido de tu aplicación al contenedor
COPY ./mi-aplicacion /var/www/html/

# Copiar las fuentes al contenedor
COPY ./Fuentes /var/www/html/Fuentes

# Aseguramos que Apache pueda servir los archivos
RUN chown -R www-data:www-data /var/www/html

# Exponemos el puerto 80 (por defecto para Apache)
EXPOSE 80
