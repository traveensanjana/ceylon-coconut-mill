# Use official PHP + Apache image
FROM php:8.2-apache

# Install the mysqli extension so our PHP files can talk to MySQL
RUN docker-php-ext-install mysqli

# Copy all website files into Apache's web root
COPY . /var/www/html/

# Apache listens on port 80 by default
EXPOSE 80
