FROM php:8.4-fpm

# Set timezone environment variable (can be overridden at build/run time)
ARG TZ=Asia/Singapore
ENV TZ=${TZ}

# Install system dependencies and PHP extensions commonly required by apps: 
# gd, pdo_mysql, mysqli, mbstring, zip. Keep image lean and cleanup apt lists.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    ca-certificates \
    curl \
    git \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    tzdata \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql mbstring zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer binary from the official Composer image (small and reliable)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application code. In production you may prefer to copy a limited set of files
# and avoid embedding secrets (.env) into images — use runtime env vars or Docker secrets.
COPY . /var/www/html

# Create the uploads/reports directory and set permissions
RUN mkdir -p /var/www/html/uploads/reports \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 775 /var/www/html/uploads

# Ensure correct permissions for php-fpm user (www-data)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose php-fpm socket port; nginx will proxy to this (internal only)
EXPOSE 9000

# Use php-fpm as the container entrypoint
CMD ["php-fpm"]
