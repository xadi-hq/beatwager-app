# Multi-stage build for Laravel 12 with PHP 8.4
# Production-ready with development conveniences

# Stage 1: Base PHP image with extensions
FROM php:8.4-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    libxml2-dev \
    postgresql-dev \
    nodejs \
    npm \
    zip \
    unzip \
    supervisor \
    shadow

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy custom PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Stage 2: Development image
FROM base AS development

# Accept build arguments for user/group ID
ARG USER_ID=1001
ARG GROUP_ID=1001

# Create user with specific UID/GID matching host user
RUN if [ "$USER_ID" != "82" ]; then \
        deluser www-data 2>/dev/null || true; \
        delgroup www-data 2>/dev/null || true; \
        addgroup -g ${GROUP_ID} www-data; \
        adduser -D -u ${USER_ID} -G www-data www-data; \
    fi

# Install Xdebug for development
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

# Copy Xdebug configuration
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Expose port 8000 for Laravel's built-in server
EXPOSE 8000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:8000/api/health || exit 1

# Default command for development (will be overridden by docker-compose)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

# Stage 3: Production image
FROM base AS production

# Copy application files
COPY . .

# Install PHP dependencies (no dev dependencies)
RUN composer install --no-interaction --no-progress --no-dev --optimize-autoloader

# Install Node dependencies and build frontend
RUN npm ci && npm run build

# Remove node_modules after build
RUN rm -rf node_modules

# Set proper permissions
RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# Optimize Laravel
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Expose port
EXPOSE 8000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:8000/api/health || exit 1

# Use supervisor to manage multiple processes
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
