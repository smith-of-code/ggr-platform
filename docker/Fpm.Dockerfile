FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    wget \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    unzip \
    libzip-dev \
    zip \
    libpq-dev \
    supervisor
RUN pecl install xdebug-3.3.1

# Install PHP extensions
RUN docker-php-ext-install zip pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

RUN docker-php-ext-enable xdebug

ADD ./php.ini /usr/local/etc/php/php.ini

# Add supervisor configuration
COPY ./supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY ./supervisor/horizon.conf /etc/supervisor/conf.d/horizon.conf
COPY ./supervisor/php-fpm.conf /etc/supervisor/conf.d/php-fpm.conf


# Создаём пользователя внутри контейнера с UID/GID как у хост-системы
ARG USER_ID
ARG GROUP_ID
# Удаляем пользователя www-data если он уже существует (чтобы освободить UID)
RUN userdel -f www-data 2>/dev/null || true

# Удаляем группу laravel если она существует
RUN groupdel laravel 2>/dev/null || true

# Находим существующую группу с нужным GID и удаляем её, либо создаём новую
RUN EXISTING_GROUP=$(getent group ${GROUP_ID} | cut -d: -f1) && \
    if [ ! -z "$EXISTING_GROUP" ]; then \
        groupdel $EXISTING_GROUP; \
    fi || true

# Создаём группу laravel с нужным GID
RUN groupadd -g ${GROUP_ID} laravel

# Создаём пользователя laravel с нужным UID и GID
RUN useradd -u ${USER_ID} -g ${GROUP_ID} -m -s /bin/bash laravel


RUN curl -sL https://deb.nodesource.com/setup_22.x | bash -
RUN apt-get install -y nodejs

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Get latest Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# Create system user to run Composer and Artisan Commands

# Create supervisor log directory
RUN mkdir -p /var/log/supervisor && chown laravel:laravel /var/log/supervisor

USER laravel
# Set working directory
WORKDIR /var/www

CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
