#!/bin/bash

set -ev

composer create-project typo3/flow-base-distribution ../flow-dist
cd ../flow-dist

cat composer.json | sed 's/^\}$/, "minimum-stability": "dev", "prefer-stable": true }/' > composer.json.new
mv composer.json.new composer.json

composer require aimeos/aimeos-flow dev-master
rm -rf Packages/Application/Aimeos.Shop
mv ../aimeos-flow Packages/Application/Aimeos.Shop
composer dump-autoload

mysql -e 'create database aimeos;'
printf "TYPO3:\n  Flow:\n    persistence:\n      backendOptions:\n        dbname: 'aimeos'\n        user: 'root'" > Configuration/Testing/Settings.yaml
