FROM php:8.2-cli

# Instala dependencias y extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install intl mbstring zip pdo pdo_pgsql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN useradd -m symfonyuser

WORKDIR /var/www/html

RUN chown symfonyuser:symfonyuser /var/www/html

USER symfonyuser

COPY --chown=symfonyuser:symfonyuser composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts

COPY --chown=symfonyuser:symfonyuser . .

RUN php bin/console cache:clear || true

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]