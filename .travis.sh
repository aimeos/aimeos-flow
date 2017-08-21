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

composer require --no-update "aimeos/aimeos-flow:2017.07.*" satooshi/php-coveralls:dev-master
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
  name: 'Aimeos Extadm'
  uriPattern: '{site}/extadm<ExtadmShopRoutes>'
  subRoutes:
    ExtadmShopRoutes:
      package: 'Aimeos.Shop'
      suffix:  'Extadm'
-
  name: 'Aimeos Jqadm'
  uriPattern: '{site}/jqadm<JqadmShopRoutes>'
  subRoutes:
    JqadmShopRoutes:
      package: 'Aimeos.Shop'
      suffix:  'Jqadm'
-
  name: 'Aimeos Jsonadm'
  uriPattern: '{site}/jsonadm<JsonadmShopRoutes>'
  subRoutes:
    JsonadmShopRoutes:
      package: 'Aimeos.Shop'
      suffix:  'Jsonadm'
-
  name: 'Aimeos Jsonapi'
  uriPattern: '{site}/jsonapi<JsonapiShopRoutes>'
  subRoutes:
    JsonapiShopRoutes:
      package: 'Aimeos.Shop'
      suffix:  'Jsonapi'
-
  name: 'Aimeos Account'
  uriPattern: '{site}/myaccount<AccountShopRoutes>'
  subRoutes:
    AccountShopRoutes:
      package: 'Aimeos.Shop'
      suffix:  'Account'
-
  name: 'Default'
  uriPattern: '{site}<DefaultSubroutes>'
  subRoutes:
    'DefaultSubroutes':
      package: 'Aimeos.Shop'
      suffix:  'Default'
-
  name: 'Update'
  uriPattern: '{site}<UpdateSubroutes>'
  subRoutes:
    'UpdateSubroutes':
      package: 'Aimeos.Shop'
      suffix:  'Update'
" > Configuration/Routes.yaml
