# ─────────────────────────────────────────────────────────────────────
# Auth Service — PHP 8.2 CLI image
# Laravel 11 · Pest 2 · PHPStan 8 · Redis · BCrypt · RS256
# ─────────────────────────────────────────────────────────────────────

FROM php:8.2-cli

# ── System dependencies ───────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    default-mysql-client \   
    # mysql client needed by test runner (CREATE DATABASE IF NOT EXISTS)
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        bcmath \
        pcntl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ── Redis extension ───────────────────────────────────────────────────
RUN pecl install redis \
    && docker-php-ext-enable redis

# ── PHP configuration ─────────────────────────────────────────────────
COPY docker/php/php.ini /usr/local/etc/php/conf.d/local.ini

# ── Composer 2 ────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── Working directory ─────────────────────────────────────────────────
WORKDIR /var/www

# ── Install Composer dependencies (layer cache — runs only when composer.json changes) ──
COPY composer.json composer.lock ./
RUN composer install \
    --no-scripts \
    --no-autoloader \
    --no-interaction \
    --prefer-dist

# ── Copy application source ───────────────────────────────────────────
COPY . .

# ── Generate optimised autoloader ────────────────────────────────────
RUN composer dump-autoload --optimize

# ── Directories required at runtime ──────────────────────────────────
RUN mkdir -p \
    storage/keys \        
    storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    tests/keys \
    && chmod 700 storage/keys tests/keys \
    && chmod -R 755 storage bootstrap/cache

EXPOSE 8000
