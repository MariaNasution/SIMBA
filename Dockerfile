# syntax=docker/dockerfile:1

# --- Build stage: install PHP dependencies ---
FROM composer:2.7 AS vendor

WORKDIR /app

# Copy only composer files for dependency install
COPY --link composer.json composer.lock ./

# Install PHP dependencies (no scripts, no dev)
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction --optimize-autoloader

# --- Build stage: node assets (optional, for Laravel Mix/Vite) ---
FROM node:20-alpine AS nodebuild
WORKDIR /app

# Copy only package files and resources for asset build
COPY --link package.json package-lock.json ./
COPY --link resources/ ./resources/
COPY --link public/ ./public/

# Install node dependencies and build assets if needed
RUN npm ci && npm run build || echo "No build script, skipping."

# --- Final stage: production image ---
FROM php:8.2-fpm-alpine AS final

# Install system dependencies
RUN apk add --no-cache \
    icu-dev libzip-dev zlib-dev libpng-dev libjpeg-turbo-dev freetype-dev \
    oniguruma-dev bash curl git mysql-client \
    && docker-php-ext-install intl pdo pdo_mysql zip gd mbstring

# Install additional PHP extensions for Laravel
RUN apk add --no-cache libxml2-dev && docker-php-ext-install xml

# Install Composer (for artisan at runtime)
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Create non-root user
RUN addgroup -S appgroup && adduser -S appuser -G appgroup

WORKDIR /app

# Copy application code (excluding .env, .git, etc. via .dockerignore)
COPY --link . .

# Copy vendor from build stage
COPY --from=vendor /app/vendor /app/vendor

# Copy built assets from nodebuild (if any)
COPY --from=nodebuild /app/public /app/public

# Set permissions for storage and bootstrap/cache
RUN chown -R appuser:appgroup storage bootstrap/cache && \
    chmod -R ug+rwx storage bootstrap/cache

USER appuser

# Expose port 8000 for Laravel's built-in server
EXPOSE 8000

# Default command: run Laravel's built-in server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
