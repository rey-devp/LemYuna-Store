# Stage 1: Build frontend assets
FROM node:18-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: Production PHP & Nginx server
FROM webdevops/php-nginx:8.1

# Install MongoDB extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Nginx settings
ENV WEB_DOCUMENT_ROOT=/app/public
ENV WEB_DOCUMENT_INDEX=index.php
ENV PHP_DATE_TIMEZONE=Asia/Jakarta

WORKDIR /app
COPY . .

# Copy built assets from Stage 1
COPY --from=node-builder /app/public/build ./public/build

# Install Laravel production dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Set permissions
RUN chown -R application:application /app/storage /app/bootstrap/cache

EXPOSE 80
