FROM php:8.2-cli

# 1. install system deps
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

# 2. pull in Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 3. copy only the files you need for install, including your example env
COPY composer.json composer.lock ./
COPY .env.example .env

# 4. install & generate key
RUN composer install --no-dev --optimize-autoloader \
 && php artisan key:generate \
 && php artisan migrate --force \
 && php artisan db:seed --force

# 5. copy rest of the app
COPY . .

EXPOSE 10000
CMD ["sh","-lc","php artisan serve --host=0.0.0.0 --port $PORT"]
