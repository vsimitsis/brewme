FROM php:7.2-fpm

RUN apt-get update && \
    apt-get install -y \
    libpng-dev \
    libfreetype6-dev \
    libjpeg-dev \
    libxpm-dev \
    libgd-dev \
    libmcrypt-dev \
    libpq-dev \
    libwebp-dev && \
    docker-php-ext-configure gd \
    --with-freetype-dir=/usr/include/ \
    --with-jpeg-dir=/usr/include/ \
    --with-xpm-dir=/usr/include \
    --with-webp-dir=/usr/include/

RUN docker-php-ext-install mysqli pdo pdo_mysql
