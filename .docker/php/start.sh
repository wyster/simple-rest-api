#!/bin/bash

if [ $COMPOSER_INSTALL == "1" ]; then
    composer global require hirak/prestissimo
    composer install --prefer-dist --no-progress --no-suggest
fi

if [ $ENABLE_XDEBUG == "1" ]; then
    docker-php-ext-enable xdebug
fi

./vendor/bin/phinx migrate

docker-php-entrypoint php-fpm
