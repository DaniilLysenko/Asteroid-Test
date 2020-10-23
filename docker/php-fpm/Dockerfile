FROM php:7.4-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        git \
        supervisor \
# Used by composer
        unzip \
# Used as part of build to check php version
        bc \
# Contains /bin/kill, used by supervisor script
        procps \
# Contains envsubst
        gettext-base \
# Contains cgi-fcgi which we use for liveness checks on fpm
        libfcgi-bin \
# for intl
        libicu-dev \
        libmemcached-dev \
        libssl-dev \
# For xsl
        libxslt-dev \
# For zip
        zlib1g-dev \
        libzip-dev \
# For pgsql
        libpq-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install -j$(nproc) intl
RUN docker-php-ext-install -j$(nproc) opcache
RUN docker-php-ext-install -j$(nproc) pcntl
RUN docker-php-ext-install -j$(nproc) pdo_mysql
RUN docker-php-ext-install -j$(nproc) sockets
RUN docker-php-ext-install -j$(nproc) zip

RUN curl --insecure https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer

WORKDIR /var/www/symfony