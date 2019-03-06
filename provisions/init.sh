#!/bin/bash

### Build the images needed
docker build -t php-fpm:latest /var/www/app/docker/php-fpm/
docker-compose -f /var/www/app/docker/docker-compose.yml build

cd /var/www/app

### Install composer dependencies
docker run --volume /var/www/app/code:/app --env-file=/var/www/app/docker/local.env composer:latest composer install

docker-compose -f /var/www/app/docker/docker-compose.yml up -d

