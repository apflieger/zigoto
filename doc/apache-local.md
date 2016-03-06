# Installation apache + php-fpm local

## Virtual hosts apache

### Pour php-fpm
Virtual host qui balance la requete à php-fpm
```
<VirtualHost *:80>
    ServerName fpm.zigotoo.com
    DocumentRoot "/Users/apflieger/sources/zigotoo/web"
    <Directory "/Users/apflieger/sources/zigotoo/web">
        AllowOverride All
        Require all granted
    </Directory>
    <FilesMatch \.php>
        SetHandler proxy:fcgi://127.0.0.1:9000
    </FilesMatch>
</VirtualHost>
```

### Pour app/console server:run
Virtual host qui permet d'avoir l'url dev.zigotoo.com,
la requete est forwardé à `app/console server:run`
*note que le .htaccess dans web/ n'est pas du tout pris en compte ici*
```
<VirtualHost *:80>
    ServerName dev.zigotoo.com
    ProxyPreserveHost On
    ProxyPass "/" "http://127.0.0.1:8000/"
    ProxyPassReverse "/"  "http://127.0.0.1:8000/"
</VirtualHost>
```

## Configuration php-fpm
Pour passer les variables d'environnements à php-fpm je n'ai pas trouvé mieux que de les déclarer dans la conf `/usr/local/etc/php/7.0/php-fpm.d/www.conf`
```
env[SYMFONY_ENV] = dev
env[SYMFONY_USER] = apf
env[SYMFONY_DEBUG] = 1
```
