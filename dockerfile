# Use official PHP image with Apache
FROM php:8.1-apache

# Enable PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite (optional, useful for routing)
RUN a2enmod rewrite

# Copy project files to Apache document root
COPY public/ /var/www/html/

# Copy config folder outside web root (optional)
COPY config/ /var/www/config/

# Set working directory
WORKDIR /var/www/html/

# Set permissions (optional, for Linux hosts)
RUN chown -R www-data:www-data /var/www/html /var/www/config

# Expose port 80
EXPOSE 80