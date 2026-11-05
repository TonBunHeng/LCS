FROM php:fpm

ARG PUID=1000
ARG PGID=1000

# Install system dependencies and tools
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    git \
    unzip \
    zip \
    nano \
    sudo \
    && rm -rf /var/lib/apt/lists/*

# Install modern PHP extensions using official extension installer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
    bcmath \
    exif \
    gd \
    intl \
    mysqli \
    opcache \
    pcntl \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    redis \
    zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configure user and group matching host UID/GID
RUN groupmod -o -g ${PGID} www-data || true && \
    usermod -o -u ${PUID} -g ${PGID} www-data || true

# Copy custom php.ini
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Ensure directories exist
RUN mkdir -p /var/log/php /var/www/html && \
    chown -R www-data:www-data /var/log/php /var/www

WORKDIR /var/www/html

USER www-data

EXPOSE 9000
CMD ["php-fpm"]

