#!/usr/bin/env bash
composer self-update
composer install
php bin/console doctrine:migrations:migrate -n
php bin/console cache:clear
npm install
rm -rf public/assets/ && php bin/console asset-map:compile
