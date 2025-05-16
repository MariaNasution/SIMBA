# syntax=docker/dockerfile:1

# --- Build stage: install PHP dependencies ---
FROM composer:2.7 AS vendor

# Install system dependencies for gd extension
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev

# Install and configure PHP gd extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

WORKDIR /app

# Copy only composer files for dependency caching
COPY --link composer.json composer.lock ./

# Install PHP dependencies (no scripts, no dev)
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction --optimize-autoloader

# --- Final stage: production image ---
FROM php:8.3-fpm-alpine AS app

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    libpng libpng-dev \
    libjpeg-turbo libjpeg-turbo-dev \
    freetype freetype-dev \
    libzip-dev zip unzip \
    oniguruma-dev \
    icu-dev \
    bash \
    shadow \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mbstring zip intl opcache

# Create non-root user
RUN addgroup -S appgroup && adduser -S appuser -G appgroup

WORKDIR /var/www/html

# Copy application code (excluding files via .dockerignore)
COPY --link . .

# Copy installed vendor dependencies from build stage
COPY --from=vendor /app/vendor ./vendor

# Ensure storage and bootstrap/cache are writable
RUN chown -R appuser:appgroup storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

USER appuser

# Expose port 8000 for php-fpm
EXPOSE 8000

COPY ./php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
# Entrypoint: php-fpm
CMD ["php-fpm"]