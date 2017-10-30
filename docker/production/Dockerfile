FROM php:7.0-apache

# System Dependencies.
RUN apt-get update && apt-get install -y \
		libicu-dev \
		libxml2-dev \
		git \
        --no-install-recommends

# PHP Dependencies
RUN docker-php-ext-install mbstring mysqli opcache intl xml

# Composer Installation
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    php -r "unlink('composer-setup.php');"

# Atomizer Installation
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_PROCESS_TIMEOUT 900
RUN git clone https://github.com/reimashi/atomizer.git /tmp/atomizer/ && mv /tmp/atomizer/src/* /var/www/ && cd /var/www && cd /var/www/ && composer install

ENTRYPOINT sleep 5000