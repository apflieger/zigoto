[![Build Status](https://travis-ci.org/apflieger/zigotoo.svg?branch=master)](https://travis-ci.org/apflieger/zigotoo)
[![codecov.io](https://codecov.io/github/apflieger/zigotoo/coverage.svg?branch=master)](https://codecov.io/github/apflieger/zigotoo?branch=master)

# Zigotoo
[prod](http://www.zigotoo.com)

## Installation

### PHP

* projet en 7.0.* Les unix ont souvent un php deja installé, le virer ou le mettre à la bonne version.
* installer composer
* installer pdo_pgsql (`brew install php70-pdo-pgsql` ou `apt-get install php7-pgsql`)
* installer un debugger genre xdebug

### node

* installer node
* `npm install -g gulp-cli`

### setup

* Déclarer une variable d'environnement `SYMFONY_USER=trigramme`
* `composer install`
* créer le schema de la base de dev : `php app/console doctrine:schema:create`
* créer le schema de la base de test : `php app/console doctrine:schema:create --env="test"`

## Usage

* pour lancer le serveur : `php app/console server:run`
* pour lancer les tests : `bin/phpunit`
* pour faire un test coverage : `bin/phpunit --coverage-html app/coverage`
* pour rebuilder le front : `gulp`
* pour watch le js : `gulp watch`
