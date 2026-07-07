FROM php:8.2-apache

# Install the mysqli extension needed to connect to your database
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy your website files to the Apache web directory
COPY . /var/www/html/

# Ensure correct file permissions and ownership for Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80 for Render
EXPOSE 80
