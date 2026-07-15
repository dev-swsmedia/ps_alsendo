ARG PHP_VERSION
FROM php:${PHP_VERSION}-cli

# Install Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Set environment variables
ARG XDEBUG_REMOTE_ENABLE

ARG XDEBUG_CLIENT_HOST

ARG XDEBUG_CLIENT_PORT

ARG XDEBUG_IDEKEY

# Set Xdebug configuration
RUN if [ "${XDEBUG_REMOTE_ENABLE}" = "1" ]; then \
    echo "xdebug.remote_enable=${XDEBUG_REMOTE_ENABLE}" >> /usr/local/etc/php/conf.d/xdebug.ini; \
    echo "xdebug.client_host=${XDEBUG_CLIENT_HOST}" >> /usr/local/etc/php/conf.d/xdebug.ini; \
    echo "xdebug.client_port=${XDEBUG_CLIENT_PORT}" >> /usr/local/etc/php/conf.d/xdebug.ini; \
    echo "xdebug.idekey=${XDEBUG_IDEKEY}" >> /usr/local/etc/php/conf.d/xdebug.ini; \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini; \
    echo "xdebug.mode=debug,develop" >> /usr/local/etc/php/conf.d/xdebug.ini; \
    echo "xdebug.cli_color=0" >> /usr/local/etc/php/conf.d/xdebug.ini; \
fi


# Instalacja niezbędnych narzędzi i zależności
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Instalacja Composera
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Ustawienie katalogu roboczego
WORKDIR /app

# Expose port 9000 for Xdebug
EXPOSE 9000