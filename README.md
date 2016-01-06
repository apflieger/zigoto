Installation
============

PHP
---
* projet en 7.0. Les unix ont souvent un php deja installé, le virer ou le mettre à la bonne version.
* installer composer
* installer un debugger genre xdebug

mysql
-----
* installer un service mysql
* créer deux bases : une pour l'app et une pour les tests unitaires
* créer un ou deux users et leur donner les droits pour requeter les bases

node
----
* installer node
* `npm install -g gulp-cli`

setup
-----
* `composer install`
* renseigner les bdd et users précédement créés, on peut laisser les valeurs par défaut pour le reste
* créer le schema de la base de dev : `php app/console doctrine:schema:create`
* créer le schema de la base de test : `php app/console doctrine:schema:create --env="test"`

Usage
-----
* pour lancer les tests : `./bin/phpunit -c app`
* pour faire un test coverage : `./bin/phpunit -c app --coverage-html app/coverage`
* pour lancer le serveur : `php app/console server:run`
* pour rebuilder le front : `gulp`
* pour watch le js : `gulp watch`