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

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/var

RUN bash -c "cat > /etc/apache2/conf-available/symfony.conf <<EOF
<Directory /var/www/html/public>
    AllowOverride All
</Directory>
EOF" && a2enconf symfony

EXPOSE 80

CMD ["apache2-foreground"]