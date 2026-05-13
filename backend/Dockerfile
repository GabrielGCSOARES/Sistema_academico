# =========================
# Stage 1 - Composer
# =========================
FROM composer:2.8 AS composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# =========================
# Stage 2 - PHP
# =========================
FROM php:8.3-fpm-alpine

# Dependências do sistema
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

# Extensões PHP
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

# Diretório
WORKDIR /var/www

# Copiar composer instalado
COPY --from=composer /app/vendor ./vendor

# Copiar projeto
COPY . .

# Permissões Laravel
RUN chown -R www-data:www-data /var/www && \
    chmod -R 755 storage bootstrap/cache

# Cache otimizado
RUN php artisan config:cache || true && \
    php artisan route:cache || true && \
    php artisan view:cache || true

EXPOSE 9000

CMD ["php-fpm"]