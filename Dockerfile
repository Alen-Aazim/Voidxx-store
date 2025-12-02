# Use official PHP image with Apache
FROM php:8.2-apache

# Enable necessary PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy project files into the container
COPY . /var/www/html/

# Give Apache permission to access the uploads folder
RUN chown -R www-data:www-data /var/www/html/uploads

# Enable Apache rewrite (optional but recommended)
RUN a2enmod rewrite

EXPOSE 80
