# ──────────────────────────────────────────────
# STAGE 1: COMPOSER (PHP Dependencies)
# ──────────────────────────────────────────────
FROM composer:latest as composer_stage
WORKDIR /app
COPY composer*.json ./
# Use --ignore-platform-reqs since we don't have a lock file and some extensions might be checked during install
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev --ignore-platform-reqs

# ──────────────────────────────────────────────
# STAGE 2: NODE (Vite Frontend Assets)
# ──────────────────────────────────────────────
FROM node:20-alpine as node_stage
WORKDIR /app
COPY . .
RUN npm install && npm run build
# DEBUG: Verify compiled assets
RUN find public/build -type f || echo "Build failed to generate files!"

# ──────────────────────────────────────────────
# STAGE 3: FINAL PRODUCTION IMAGE
# ──────────────────────────────────────────────
FROM php:8.4-fpm-alpine

LABEL name="SmartGate Hybrid Service"
LABEL type="Laravel-Python-Deployment"

# 1. Install system dependencies
# Install Nginx, Python3, and Pip for bridge service
RUN apk add --no-cache \
    nginx \
    python3 \
    py3-pip \
    libzip-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libavif-dev \
    zip \
    unzip \
    git \
    curl \
    mysql-client \
    linux-headers \
    zlib-dev \
    oniguruma-dev \
    icu-dev

# 2. Configure and Install PHP Extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-avif && \
    docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# 3. Setup Project Workdir
WORKDIR /var/www/html

# 4. Copy backend and frontend from previous stages
# Cache bust with app version matching package.json
ARG APP_VERSION=1.0.6
COPY --from=composer_stage --chown=www-data:www-data /app /var/www/html
# Explicitly copy build folder last to ensure it overwrites everything in public/build
COPY --from=node_stage --chown=www-data:www-data /app/public/build /var/www/html/public/build

# 5. Setup Python Requirements for bridge service
COPY requirements.txt .
# Railway uses Alpine, we need to allow system package break if pip refuses
RUN pip install --no-cache-dir -r requirements.txt --break-system-packages || true

# 6. Setup Configuration
RUN rm -rf /etc/nginx/http.d/*
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# 7. Configure Permissions and Nginx User
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
    # Fix Nginx permissions to run as non-root / unprivileged
    mkdir -p /var/lib/nginx/tmp /var/log/nginx && \
    chown -R www-data:www-data /var/lib/nginx /var/log/nginx /etc/nginx/http.d /var/www/html/public && \
    chmod -R 777 /var/lib/nginx /var/log/nginx && \
    # Remove default user if it causes permission issues in unprivileged containers
    sed -i 's/user nginx;//g' /etc/nginx/nginx.conf

# 8. Environment Handling for Railway
# Railway sets $PORT automatically for web traffic
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV PORT=8080
EXPOSE $PORT

# 9. Start Laravel and Python Services via startup script
CMD ["/usr/local/bin/start.sh"]
