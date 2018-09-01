# Do good (with Bunq)

Authors:
 - Rick van der Linden
 - Rody Moolenaar
 - Daniël Hansen
 
 ## Setup
 
```bash
composer install
rm -rf ./var/doctrine/*
php ./bin/do-good orm:schema-tool:update --force
```