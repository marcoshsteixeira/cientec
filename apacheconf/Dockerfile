FROM php:8.3-apache

# Install php or apache dependencies
RUN docker-php-ext-install pdo_mysql opcache \
    && apt-get upgrade \
    && apt-get update -y \
    && apt-get install -y \
    zip unzip git

# Replace v-hosts file
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Enable php mod_rewrite and mod_headers.
RUN cp /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/ && \
    cp /etc/apache2/mods-available/headers.load /etc/apache2/mods-enabled/

# Node install
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash
RUN apt-get install -y nodejs 

# Composer install
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Execute npm and composer
CMD npm install -g npm && if [ -e /var/www/html/package.json ]; then npm install ; fi && if [ -e /var/www/html/composer.json ]; then composer update ; fi && apachectl -D FOREGROUND

# Ensures the 80 door are open
EXPOSE 80