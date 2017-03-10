#!/bin/bash

set -ev

cat composer.json | sed 's/^\}$/,\
    "minimum-stability": "dev",\
    "prefer-stable": true,\
    "repositories": [\
        {\
            "type": "vcs",\
            "url": "https:\/\/github.com\/aimeos\/php-coveralls.git"\
        }\
    ],\
    "extra": {\
        "installer-paths": {\
            "Packages\/Extensions\/{$name}\/": ["type:aimeos-extension"]\
        }\
    }\
}/' > composer.json.new
mv composer.json.new composer.json

composer require --no-update aimeos/aimeos-flow:dev-master satooshi/php-coveralls:dev-master
composer update
rm -rf Packages/Application/Aimeos.Shop
mv ../aimeos-flow Packages/Application/Aimeos.Shop
composer dump-autoload

mysql -e 'create database aimeos;'

printf "Neos:\n  Flow:\n    persistence:\n      backendOptions:\n        dbname: 'aimeos'\n        user: 'root'\n    http:\n      baseUri: http://aimeos.org/\n" > Configuration/Testing/Settings.yaml
printf "Aimeos:\n  Shop:\n    flow:\n      disableSites: 0\n" >> Configuration/Testing/Settings.yaml

printf "\n-\n  name: 'Aimeos'\n  uriPattern: '{site}/<AimeosShopRoutes>'\n  subRoutes:\n    'AimeosShopRoutes':\n      package: 'Aimeos.Shop'" > Configuration/Routes.yaml
