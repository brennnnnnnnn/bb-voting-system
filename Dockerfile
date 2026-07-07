FROM php:8.2-apache

# Install the mysqli extension needed to connect to your database
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy your website files to the Apache web directory
COPY . /var/www/html/

# Expose port 80 for Render
EXPOSE 80
