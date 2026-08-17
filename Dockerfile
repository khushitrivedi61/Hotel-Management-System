# Official PHP Apache Image
FROM php:8.2-apache

# Install PDO MySQL extensions required by the application
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache Rewrite Module
RUN a2enmod rewrite

# Copy project files into Web Root
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Expose HTTP Port
EXPOSE 80
