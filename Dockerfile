# 1) Use PHP 8.2 so it matches your composer.lock
FROM php:8.2-cli

# 2) Install system packages + dev headers
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

# 3) Get Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 4) Copy only the files needed for Composer & .env
COPY composer.json composer.lock .env.example ./

# 5) Prepare .env so artisan commands work
RUN cp .env.example .env

# 6) Now install PHP dependencies (and run scripts safely)
RUN composer install --no-dev --optimize-autoloader

# 7) Generate the key, migrate & seed
RUN php artisan key:generate \
 && php artisan migrate --force \
 && php artisan db:seed --force

# 8) Copy the rest of your application code
COPY . .

# 9) Expose and run
EXPOSE 10000
CMD ["sh","-lc","php artisan serve --host=0.0.0.0 --port $PORT"]
