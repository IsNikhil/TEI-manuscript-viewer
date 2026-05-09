FROM php:8.2-apache

# Install system deps for ext-xsl, then enable PHP extensions
RUN apt-get update && apt-get install -y libxslt1-dev --no-install-recommends \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install xsl

# Enable mod_rewrite so .htaccess routing works
RUN a2enmod rewrite

# Set document root to /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri 's|/var/www/html|${APACHE_DOCUMENT_ROOT}|g' \
        /etc/apache2/sites-available/000-default.conf \
    && sed -ri 's|/var/www/html|${APACHE_DOCUMENT_ROOT}|g' \
        /etc/apache2/apache2.conf

# Allow .htaccess overrides
RUN sed -ri 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

COPY . /var/www/html

EXPOSE 80
