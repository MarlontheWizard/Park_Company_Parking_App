#Install PHP & Apache into Container
FROM php:7.4-apache                     

#Run apt-get command to install necessary PHP libraries
#These libraries are native to PHP (e.g mysqli, unzip, libzip-dev)
RUN apt-get update && apt-get install -y \              
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql zip


#The rewrite mod in Apache cleans the URL's of our pages so that they are simple 
RUN a2enmod rewrite

#Set the name of the server to suppress Apache warning in Docker Container log
#We are modifying the Apache configuration file 
RUN echo "Park_App_Server" >> /etc/apache2/apache2.conf


#Copy Composer from the official Composer image
#We need the composer to manage/install dependencies 
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

#Set the working directory of the Docker Container 
#Local host environment is mounted below to Docker Container, go to Files in Docker Desktop or CMD
WORKDIR /var/www/html

#Copy composer.json and composer.lock 
COPY composer.json composer.lock ./ 

#Install dependencies to container
RUN composer install

#Mount the rest of the application files
COPY . .


EXPOSE 80