#!/bin/bash

set -ev

cat composer.json | sed 's/^\}$/,\
    "minimum-stability": "dev",\
    "prefer-stable": true,\
    "extra": {\
        "installer-paths": {\
            "Resources\/Extensions\/{$name}\/": ["type:aimeos-extension"]\
        }\
    }\
}/' > composer.json.new
mv composer.json.new composer.json

composer require aimeos/aimeos-flow dev-master
rm -rf Packages/Application/Aimeos.Shop
mv ../aimeos-flow Packages/Application/Aimeos.Shop
composer dump-autoload

mysql -e 'create database aimeos;'
printf "TYPO3:\n  Flow:\n    persistence:\n      backendOptions:\n        dbname: 'aimeos'\n        user: 'root'" > Configuration/Testing/Settings.yaml
