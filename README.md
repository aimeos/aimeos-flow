<a href="https://aimeos.org/">
    <img src="https://aimeos.org/fileadmin/template/icons/logo.png" alt="Aimeos logo" title="Aimeos" align="right" height="60" />
</a>

# Aimeos Flow/Neos package

[![Build Status](https://travis-ci.org/aimeos/aimeos-flow.svg?branch=master)](https://travis-ci.org/aimeos/aimeos-flow)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/aimeos/aimeos-flow/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/aimeos/aimeos-flow/?branch=master)

The repository contains the web shop package for Flow/Neos
integrating the Aimeos e-commerce library into Flow/Neos. The package provides
controllers for e.g. faceted filter, product lists and detail views, for
searching products as well as baskets and the checkout process. A full set of
pages including routing is also available for a quick start in Flow.

[![Aimeos Flow demo](https://aimeos.org/fileadmin/user_upload/flow-demo.jpg)](http://flow.demo.aimeos.org/)

## Table of content

- [Installation](#installation)
- [Setup](#setup)
- [Hints](#hints)
- [License](#license)
- [Links](#links)

## Installation

This document is for the latest Aimeos Flow **2017.10 release and later**.

- Beta release: 2018.01
- LTS release: 2017.10

The Aimeos Flow/Neos web shop package is a composer based library that can be
installed easiest by using [Composer](https://getcomposer.org):

`composer create-project neos/flow-base-distribution myshop`

Make sure that the **database is set up and it is configured**.  If you want to
use a database server other than MySQL, please have a look into the article about
[supported database servers](https://aimeos.org/docs/Developers/Library/Database_support)
and their specific configuration.

Neos has a nice setup page for this when opening the `/setup` URL of your installation.
For Flow, this is done in your `Configuration/Settings.yaml` file and must
at least include these settings:

```yaml
Neos:
  Flow:
    persistence:
      backendOptions:
        host: '<host name or IP address>'
        dbname: '<database name>'
        user: '<database user name>'
        password: '<secret password>'
```

**Important:** The configuration file format requires each additional indention
to be two spaces. Not more, not less and no tabs at all! Otherwise, you will get
an error about an invalid configuration file format.

Then add these lines to your composer.json of your Flow/Neos project:

```
    "extra": {
        "installer-paths": {
            "Packages/Extensions/{$name}/": ["type:aimeos-extension"]
        }
    },
    "prefer-stable": true,
    "minimum-stability": "dev",
    "require": {
        "aimeos/aimeos-flow": "~2017.10",
        ...
    },
```

Afterwards, install the Aimeos shop package using

`composer update`

## Setup

To create all required tables and to add the demo data, you need to execute a
Flow console command in the base directory of your Flow application:

`./flow aimeos:setup --option=setup/default/demo:1`

In a production environment or if you don't want that the demo data gets
installed, leave out the `--option=setup/default/demo:1` option.

For **Flow only** you need to import the routes from the Aimeos web shop
package into your `Configuration/Routes.yaml` nice looking URLs. Insert the lines
below to the **beginning** of the Routes.yaml file:

```yaml
-
  name: 'Aimeos'
  uriPattern: 'shop/<AimeosShopRoutes>'
  subRoutes:
    AimeosShopRoutes:
      package: 'Aimeos.Shop'
```

It's important to import the routes from the Aimeos web shop package before the
`FlowSubroutes` lines. If you add it afterwards, the default Flow routes will
match first and you will get an error that the requested package/action wasn't
found.

Now Flow would basically know which controller/action it shall execute. But with
Neos, one additional step is needed:

Add the following **PrivilegeTarget** to `Configuration/Policy.yaml`

```yaml
privilegeTargets:
  Neos\Flow\Security\Authorization\Privilege\Method\MethodPrivilege:
    'MyShop:AllActions':
      matcher: 'method(Aimeos\Shop\Controller\(.*)Controller->(.*)Action())'

roles:
  'Neos.Flow:Everybody':
    privileges:
      -
        privilegeTarget: 'MyShop:AllActions'
        permission: GRANT
```

The above will grant access to **all** Aimeos Controller/Actions pairs, for
**everyone** - probably not what you want. Please refine to your needs!

Then, you should be able to call the catalog list page in your browser using

```http://<your web root>/shop/list```

For the administration interface you have to setup authenticaton first and log
in before you will be able to get into the shop management interface:

```http://<your web root>/shop/admin```

## Hints

To simplify development, you should configure to use no content cache. You can
do this in the `Configuration/Settings.yaml` file of your Flow/Neos application
by adding these lines at the bottom:

```yaml
Aimeos:
  Shop:
    flow:
      cache:
        name: None
```

## License

The Aimeos Flow/Neos package is licensed under the terms of the LGPLv3 license
and is available for free.

## Links

* [Web site](https://aimeos.org/Flow)
* [Documentation](https://aimeos.org/docs/Flow)
* [Forum](https://aimeos.org/help)
* [Issue tracker](https://github.com/aimeos/aimeos-flow/issues)
* [Composer packages](https://packagist.org/packages/aimeos/aimeos-flow)
* [Source code](https://github.com/aimeos/aimeos-flow)
