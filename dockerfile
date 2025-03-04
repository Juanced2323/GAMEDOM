# Dockerfile
FROM php:8.0-apache

# Instalamos dependencias necesarias y la extensión mysqli
RUN apt-get update && \
    apt-get install -y libmariadb-dev && \
    docker-php-ext-install mysqli pdo pdo_mysql
