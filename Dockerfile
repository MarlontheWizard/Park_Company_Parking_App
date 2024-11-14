# Install PHP & Apache into the Container
FROM php:8.0-apache

# Install necessary PHP libraries
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql zip

# Configure Git to trust the /var/www/html directory
RUN git config --global --add safe.directory /var/www/html


# Enable Apache rewrite module
RUN a2enmod rewrite

# Set the name of the server to suppress Apache warning in logs
RUN echo "ServerName Park_App_Server" >> /etc/apache2/apache2.conf

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory inside the Docker container
WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html

RUN chmod -R 755 /var/www/html

# Copy the composer.json and composer.lock files
COPY composer.json ./

# Copy the rest of the application files
COPY . .

# Install Composer dependencies
RUN composer install --verbose

# Expose the port
EXPOSE 80