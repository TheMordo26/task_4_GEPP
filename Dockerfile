FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install intl mbstring zip pdo pdo_pgsql

RUN useradd -m symfonyuser

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN chown symfonyuser:symfonyuser ./

USER symfonyuser

RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts

COPY --chown=symfonyuser:symfonyuser . .

RUN php bin/console cache:clear || true

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]