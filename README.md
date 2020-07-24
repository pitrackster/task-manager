# Simple Task Manager

A task manager that allow user to define categories, tasks per category and add events per task.

User can also set holidays to remove vancancies from statistics


## install

- clone this repository
- cd `task-manager`
- `sudo setfacl -R -m u:www-data:rwX -m u:`whoami`:rwx  var/log/*`
- `sudo setfacl -dR -m u:www-data:rwX -m u:`whoami`:rwx  var/log/*`
- `composer install`
- `php bin/console doctrine:database:create`
- `php bin/console make:migration`
- `php bin/console doctrine:migrations:migrate`

## scripts

- `yarn encore dev`
- `php bin/console make:entity`
- `php bin/console make:migration`
- `php bin/console make:crud`
- `php bin/console make:controller MyController`
- `php bin/console doctrine:migrations:migrate`

## apache conf

```conf
DocumentRoot /var/www/html/task-manager/public
DirectoryIndex /index.php

<Directory /var/www/my-apache>
   Options FollowSymlinks
</Directory>

<Directory /var/www/html/task-manager/public>
    AllowOverride None
    Order Allow,Deny
    Allow from All
    <IfModule mod_rewrite.c>
        Options -MultiViews
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ index.php [QSA,L]
    </IfModule>  
</Directory>

<Directory /var/www/html/task-manager/public/bundles>
    FallbackResource disabled
</Directory>

ErrorLog /var/log/apache2/task-manager.log
CustomLog /var/log/apache2/task-manager-access.log combined
```

- you can access your application with http://localhost/task-manager/public

## TODO

- excel export https://phpspreadsheet.readthedocs.io
- simple API (view stats, add event)
