FROM webdevops/php-nginx:8.1

# Install ekstensi MongoDB
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Pengaturan Nginx agar membaca folder /public Laravel
ENV WEB_DOCUMENT_ROOT=/app/public
ENV WEB_DOCUMENT_INDEX=index.php
ENV PHP_DATE_TIMEZONE=Asia/Jakarta

WORKDIR /app
COPY . .

# Install dependensi Laravel (mengabaikan paket dev)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Hak akses folder cache & storage
RUN chown -R application:application /app/storage /app/bootstrap/cache

# Generate key (bisa di-override lewat env variables Render nanti)
# RUN php artisan key:generate --force
