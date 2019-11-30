#!/usr/bin/env bash
echo 'Run start.sh'

bash /wait-for.sh php:9000 -t 0 -- echo "Php fpm started"

nginx -g 'daemon off;'
