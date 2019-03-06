FROM php-fpm

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
ADD ./xdebug.ini /var/
RUN cat /var/xdebug.ini >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
