#!/bin/bash
docker-compose -f /var/www/app/docker/docker-compose.yml build
docker-compose -f /var/www/app/docker/docker-compose.yml up -d
