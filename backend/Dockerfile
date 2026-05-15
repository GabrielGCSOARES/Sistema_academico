FROM composer:2.8 AS composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    icu-dev \
    mysql-client \
    nodejs \
    npm

RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg && \
    docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl

WORKDIR /var/www

COPY --from=composer /app/vendor ./vendor
COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY . .

RUN chown -R www-data:www-data /var/www && \
    chmod -R 755 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
