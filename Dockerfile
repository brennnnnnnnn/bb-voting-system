FROM php:8.2-apache

# Install the mysqli extension needed to connect to your database
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy all files to the Apache web directory
COPY . /var/www/html/

# If the files are nested inside a 'sistempengundian' folder, move them to the root /var/www/html/
RUN if [ -d "/var/www/html/sistempengundian" ]; then \
        cp -r /var/www/html/sistempengundian/* /var/www/html/ 2>/dev/null || true; \
        cp -r /var/www/html/sistempengundian/.* /var/www/html/ 2>/dev/null || true; \
        rm -rf /var/www/html/sistempengundian; \
    fi

# Ensure correct file permissions and ownership for Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80 for Render
EXPOSE 80
