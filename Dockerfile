# Official PHP Apache Image
FROM php:8.2-apache

# Install PDO MySQL and PDO SQLite extensions for zero-config fallback
RUN apt-get update && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite mysqli

# Enable Apache Rewrite Module
RUN a2enmod rewrite

# Copy project files into Web Root
COPY . /var/www/html/

# Set working directory & permissions for database directory
WORKDIR /var/www/html/
RUN chmod -R 777 /var/www/html/database

# Expose HTTP Port
EXPOSE 80
