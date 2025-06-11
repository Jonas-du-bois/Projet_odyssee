#!/bin/bash

echo "=== Nettoyage du cache sur Heroku ==="
heroku run "php artisan config:clear" --app=backend-breitling-league
heroku run "php artisan route:clear" --app=backend-breitling-league  
heroku run "php artisan cache:clear" --app=backend-breitling-league
heroku run "php artisan view:clear" --app=backend-breitling-league

echo "=== Vérification des routes ==="
heroku run "php artisan route:list --path=/docs" --app=backend-breitling-league

echo "=== Test de la route docs ==="
curl -I https://backend-breitling-league-e1d83468309e.herokuapp.com/docs
