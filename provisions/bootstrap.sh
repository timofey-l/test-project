#!/bin/bash

apt-get update
apt-get upgrade -y
apt-get install -y git
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh
usermod -aG docker vagrant
apt install docker-compose -y
dpkg -r --force-depends golang-docker-credential-helpers

service docker status
service docker start

echo "alias console='docker-compose -f /var/www/app/docker/docker-compose.yml exec php /var/app/bin/console'" >> /home/vagrant/.bashrc
echo "alias composer='docker run --volume /var/www/app/code:/app --env-file=/var/www/app/docker/local.env composer:latest composer'" >> /home/vagrant/.bashrc
echo "cd /var/www/app" >> /home/vagrant/.bashrc
