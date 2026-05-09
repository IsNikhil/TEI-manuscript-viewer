FROM php:8.2-apache

RUN apt-get update && apt-get install -y libxslt1-dev --no-install-recommends \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install xsl

RUN a2dismod mpm_event && a2enmod mpm_prefork rewrite

# Point document root to the /public subfolder
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' \
        /etc/apache2/sites-available/000-default.conf

# Allow .htaccess overrides (needed for the PHP router)
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

COPY . /var/www/html

EXPOSE 80
