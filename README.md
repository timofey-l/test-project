# Services

- `[ip]:80` PHP application
- `[ip]:8080` PhpMyAdmin
- `[ip]:1080` MailDev web interface to check caught emails

Since composer runs in a separate container and some packages require PHP extensions which this container doen't have you should:

- Run `composer require` with `--ignore-platform-reqs`
- Make sure that required extensions added to `./docker/php-fpm/Dockerfile`

# Additional setup

- To set working directory to `/var/www/app` after `vagrant ssh` command:  
    Add line `cd /var/www/app` to the file `/home/vagrant/.bashrc`  

- To use `artisan` command after `vagrant ssh`:  
    Add line `alias console='docker-compose -f /var/www/app/docker/docker-compose.yml exec php /var/app/bin/console'` to the file `/home/vagrant/.bashrc`

- To use `composer` command:  
    Add line `alias composer='docker run --volume /var/www/app/code:/app --env-file=/var/www/app/docker/local.env composer:latest composer'` to the file `/home/vagrant/.bashrc`
