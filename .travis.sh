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

printf "
Neos:
  Flow:
    persistence:
      backendOptions:
        dbname: 'aimeos'
        user: 'root'
    http:
      baseUri: http://aimeos.org/
Aimeos:
  Shop:
    flow:
      disableSites: 0
" > Configuration/Testing/Settings.yaml

printf "
-
  name: 'Aimeos Myaccount'
  uriPattern: '{site}/myaccount<AccountSubroutes>'
  defaults:
    '@package':    'Aimeos.Shop'
  subRoutes:
    'AccountSubroutes':
      package: 'Aimeos.Shop'
      suffix:  'Myaccount'
-
  name: 'Aimeos Jqadm'
  uriPattern: '{site}/jqadm<JqadmSubroutes>'
  defaults:
    '@package':    'Aimeos.Shop'
  subRoutes:
    'JqadmSubroutes':
      package: 'Aimeos.Shop'
      suffix:  'Jqadm'
-
  name: 'Aimeos Jsonadm'
  uriPattern: '{site}/jsonadm<JsonadmSubroutes>'
  defaults:
    '@package':    'Aimeos.Shop'
  subRoutes:
    'JsonadmSubroutes':
      package: 'Aimeos.Shop'
      suffix:  'Jsonadm'
-
  name: 'Aimeos Jsonapi'
  uriPattern: '{site}/jsonapi<JsonapiSubroutes>'
  defaults:
    '@package':    'Aimeos.Shop'
  subRoutes:
    'JsonapiSubroutes':
      package: 'Aimeos.Shop'
      suffix:  'Jsonapi'
" > Configuration/Routes.yaml
