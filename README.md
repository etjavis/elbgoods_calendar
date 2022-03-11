# Projekt aufsetzten

## Docker
- Container starten: `docker-compose up -d`
- Container betreten: `docker exec -it elbgoods_calendar_php /bin/bash`
- Rechte korrigieren: `chown -R www-data:www-data ./`
- Container verlassen: STRG+D
- Container betreten als www-data: `docker exec -it --user=www-data elbgoods_calendar_php /bin/bash`

## Laravel
- Env erstellen: `cp .env.example .env`
- Alle Bibliotehken laden: `composer install`
- Laravel-Key erzeugen: `php artisan key:generate`
- Datenbank befüllen: `php artisan migrate`
- URL im Browser aufrufen: `http://localhost/api`


## Testing
- Auf dem Mysql-Server die Datenbank `elbgoods_calendar_testing` anlegen
- Im container folgenden Befehl aufrufen `php artisan test --env=testing`