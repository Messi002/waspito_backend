FROM php:8.2-cli

RUN apt-get update \
 && apt-get install -y \
      git \
      unzip \
      libpq-dev \
      libonig-dev \
      libzip-dev \
      pkg-config \
      zip \
 && docker-php-ext-install \
      pdo \
      pdo_pgsql \
      mbstring \
      zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader \
 && php artisan key:generate \
 && php artisan migrate --force \
 && php artisan db:seed --force

EXPOSE 10000
CMD ["sh","-lc","php artisan serve --host=0.0.0.0 --port $PORT"]
