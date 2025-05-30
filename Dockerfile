FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libonig-dev \
    libpq-dev \
    git \
    unzip \
    zip \
    && docker-php-ext-install intl pdo pdo_pgsql opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite

COPY . /var/www/html

WORKDIR /var/www/html

RUN groupadd -g 1000 appuser && useradd -u 1000 -g appuser -m appuser

RUN chown -R appuser:appuser /var/www/html

USER appuser

RUN php -v
RUN composer --version
RUN composer clear-cache

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

USER root

RUN [ -d /var/www/html/var ] && chown -R www-data:www-data /var/www/html/var || echo "Skipping chown, /var/www/html/var not found"

RUN echo -e "<Directory /var/www/html/public>\n\
    AllowOverride All\n\
</Directory>" > /etc/apache2/conf-available/symfony.conf && a2enconf symfony

EXPOSE 80

CMD ["apache2-foreground"]