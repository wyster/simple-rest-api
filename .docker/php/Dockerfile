ARG PHP_VERSION=7.4

FROM php:${PHP_VERSION}-fpm


RUN apt update && apt install -y \
    git \
    curl \
    zip \
    # for postgres
    libpq-dev \
    # zip
    libzip-dev \
    # intl
    zlib1g-dev libicu-dev g++

# XDebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# PostgreSQL
RUN docker-php-ext-install pdo_pgsql pgsql

# Other
RUN docker-php-ext-install zip intl

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ADD ./start.sh /start.sh
RUN chmod +x /start.sh
ENTRYPOINT /start.sh
