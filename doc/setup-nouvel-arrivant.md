# Setup du nouvel arrivant
L'ordre est important. En particulier pour la conf, à faire avant le `composer install`.

## PHP

* projet en 7.0.*, virer ou surcharger l'install existante.
* installer composer
* installer pdo_pgsql (`brew install php70-pdo-pgsql` ou `apt-get install php7-pgsql`)

## node

* installer node
* `npm install -g grunt-cli`

## Configurations

* Variable d'environnement `SYMFONY_USER=apf` (pas trouvé comment la mettre globale au systeme et pas juste dans le shell)
* Conf php `date.timezone = 'Europe/Paris'`

## build

* cloner ce repo, configurer name/email git
* `composer install`
* créer le schema de la base de dev : `app/console doctrine:schema:create`
* créer le schema de la base de test : `app/console doctrine:schema:create --env="test"`

## Usage

* pour lancer le serveur : `app/console server:run`
* pour lancer les tests : `bin/phpunit`

## Debugger

installer xdebug

## Phpstorm

* configurer php
* phpunit en run/debug 
* remote debug en zero config debug