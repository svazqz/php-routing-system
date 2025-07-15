FROM php:8.2-apache

# Install SQLite and PHP SQLite extension
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80