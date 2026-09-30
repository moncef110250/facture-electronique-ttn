FROM php:8.2-apache
RUN apt-get update && apt-get install -y libzip-dev libpng-dev libonig-dev libxml2-dev zip unzip git \
    && docker-php-ext-install zip gd mbstring \
    && a2enmod rewrite
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
WORKDIR /var/www/html
COPY . /var/www/html/
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts || echo "retry"
RUN mkdir -p uploads exports exportations && chmod -R 777 uploads exports exportations && chown -R www-data:www-data /var/www/html
EXPOSE 80
CMD ["apache2-foreground"]

