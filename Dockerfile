FROM php:8.2-apache

# Install PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copy all files to Apache document root
COPY . /var/www/html/

# Enable Apache rewrite module
RUN a2enmod rewrite

EXPOSE 80
