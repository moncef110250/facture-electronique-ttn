FROM php:8.2-apache
RUN apt-get update && apt-get install -y libzip-dev libpng-dev libonig-dev libxml2-dev unzip && docker-php-ext-install zip gd mbstring
RUN a2enmod rewrite
COPY . /var/www/html/
WORKDIR /var/www/html
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN mkdir -p uploads exports
RUN composer install --no-dev --optimize-autoloader
RUN chown -R www-data:www-data /var/www/html && chmod -R 775 /var/www/html/uploads /var/www/html/exports
EXPOSE 80
