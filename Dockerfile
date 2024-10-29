#Import php image (includes apache)
FROM php:7.4-apache                     

#Import additional packages/dependencies 
RUN docker-php-ext-install mysqli pdo pdo_mysql

#Route files to docker root folder 
COPY . /var/www/html/