# ============================================================================
# Multi-stage Dockerfile for Laravel + Python LSTM Service
# ============================================================================

# ────────────────────────────────────────────────────────────────────────────
# Stage 1: PHP/Laravel Application
# ────────────────────────────────────────────────────────────────────────────
FROM php:8.3-fpm-alpine AS laravel

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    mysql-client \
    nodejs \
    npm \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install Node dependencies and build assets
RUN npm ci && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# ────────────────────────────────────────────────────────────────────────────
# Stage 2: Python LSTM Service
# ────────────────────────────────────────────────────────────────────────────
FROM python:3.12-slim AS lstm-service

# Install system dependencies for TensorFlow
RUN apt-get update && apt-get install -y --no-install-recommends \
    gcc \
    g++ \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Copy Python service file
COPY app/Services/AI/LSTM_Service.py /app/LSTM_Service.py

# Install Python dependencies
RUN pip install --no-cache-dir \
    fastapi==0.135.3 \
    uvicorn[standard]==0.42.0 \
    tensorflow==2.18.0 \
    pandas==2.2.3 \
    scikit-learn==1.6.1 \
    holidays==0.62

# Suppress TensorFlow warnings
ENV TF_ENABLE_ONEDNN_OPTS=0
ENV TF_CPP_MIN_LOG_LEVEL=2

# Expose port
EXPOSE 8001

# Start service
CMD ["uvicorn", "LSTM_Service:app", "--host", "0.0.0.0", "--port", "8001"]

# ────────────────────────────────────────────────────────────────────────────
# Stage 3: Final Production Image (Laravel + Nginx)
# ────────────────────────────────────────────────────────────────────────────
FROM php:8.3-fpm-alpine

# Install runtime dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    mysql-client \
    libpng \
    libzip

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip gd opcache

# Copy application from builder
COPY --from=laravel /var/www /var/www

# Copy Nginx configuration
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Copy Supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy PHP-FPM configuration
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Set working directory
WORKDIR /var/www

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Create log directories
RUN mkdir -p /var/log/supervisor /var/log/nginx /var/log/php-fpm \
    && chown -R www-data:www-data /var/log/supervisor

# Expose ports
EXPOSE 80

# Start Supervisor (manages Nginx + PHP-FPM)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
