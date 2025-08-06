# syntax=docker/dockerfile:1

# Define ARGs at the top
ARG NODE_VERSION=22.14.0
ARG GO_VERSION=1.24.2

# --- Build Stage: PHP Dependencies ---
FROM composer:2.7 AS php-vendor
RUN apk add --no-cache libpng-dev libjpeg-turbo-dev freetype-dev
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd
WORKDIR /app
COPY --link composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction --optimize-autoloader

# --- Build Stage: React App ---
FROM node:${NODE_VERSION}-slim AS react-builder
WORKDIR /app
COPY --link Simba-chatting/package.json Simba-chatting/package-lock.json ./
RUN --mount=type=cache,target=/root/.npm npm ci
COPY --link Simba-chatting/public ./public
COPY --link Simba-chatting/src ./src
COPY --link Simba-chatting/scripts ./scripts
COPY --link Simba-chatting/README.md ./
RUN --mount=type=cache,target=/root/.npm npm run build

# --- Build Stage: Go Backend ---
FROM golang:${GO_VERSION}-alpine AS go-builder
RUN apk add --no-cache git
WORKDIR /app
COPY --link Simba-chatting/backend/go.mod Simba-chatting/backend/go.sum ./
RUN --mount=type=cache,target=/go/pkg/mod go mod download
COPY --link Simba-chatting/backend .
RUN --mount=type=cache,target=/go/pkg/mod CGO_ENABLED=0 GOOS=linux go build -ldflags="-s -w" -o simba-chat-backend

# --- Final Stage: Combined Image ---
FROM php:8.3-fpm-alpine

# Re-declare ARGs for this stage
ARG NODE_VERSION
ARG GO_VERSION

# Install dependencies
RUN apk add --no-cache \
    libpng libpng-dev libjpeg-turbo libjpeg-turbo-dev freetype freetype-dev \
    libzip-dev zip unzip oniguruma-dev icu-dev bash shadow \
    nodejs npm go nginx supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mbstring zip intl opcache

# Create non-root user
RUN addgroup -S appgroup && adduser -S appuser -G appgroup

# Fix Nginx directory permissions before switching user
RUN mkdir -p /var/lib/nginx/logs /var/lib/nginx/tmp/client_body /var/lib/nginx/tmp/proxy /var/lib/nginx/tmp/fastcgi /var/lib/nginx/tmp/uwsgi /var/lib/nginx/tmp/scgi \
    && chown -R appuser:appgroup /var/lib/nginx \
    && chmod -R 775 /var/lib/nginx

WORKDIR /app

# Copy Laravel app
COPY --link . .

# Copy PHP vendor
COPY --from=php-vendor /app/vendor ./vendor

# Copy React build
COPY --from=react-builder /app/build ./Simba-chatting/build

# Copy Go binary
COPY --from=go-builder /app/simba-chat-backend ./Simba-chatting/backend/simba-chat-backend
COPY --link Simba-chatting/backend/service-account.json ./Simba-chatting/backend/service-account.json

# Install serve for React
RUN npm install -g serve

# Set permissions
RUN chown -R appuser:appgroup /app storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Configure PHP-FPM
COPY ./php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Configure Nginx
COPY ./nginx-main.conf /etc/nginx/nginx.conf  
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

# Configure Supervisord
COPY ./supervisord.conf /etc/supervisord.conf

USER appuser

EXPOSE 80

CMD ["supervisord", "-c", "/etc/supervisord.conf"]