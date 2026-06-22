# ---- Stage: builder ----
FROM php:8.4-cli AS builder

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
        git curl unzip libzip-dev \
    && docker-php-ext-install pdo_mysql bcmath zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP dependencies before copying app code for better layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist

# Copy full application
COPY . .

# Regenerate autoloader with classmap optimizations
RUN composer dump-autoload --optimize --no-dev

# Install Node.js and build frontend assets
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/* \
    && npm ci \
    && npm run build \
    && rm -rf node_modules


# ---- Stage: runtime ----
FROM php:8.4-cli

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev \
    && docker-php-ext-install pdo_mysql bcmath zip opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=builder /var/www/html /var/www/html

RUN chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
