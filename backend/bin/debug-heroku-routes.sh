#!/bin/bash

echo "=== Diagnostic des routes Scribe sur Heroku ==="

echo "1. Vérification des routes..."
heroku run "php artisan route:list | grep docs" --app=backend-breitling-league

echo -e "\n2. Nettoyage du cache..."
heroku run "php artisan config:clear" --app=backend-breitling-league
heroku run "php artisan route:clear" --app=backend-breitling-league  
heroku run "php artisan cache:clear" --app=backend-breitling-league
heroku run "php artisan view:clear" --app=backend-breitling-league

echo -e "\n3. Vérification de la configuration Scribe..."
heroku run "php artisan config:show scribe" --app=backend-breitling-league

echo -e "\n4. Re-vérification des routes..."
heroku run "php artisan route:list | grep docs" --app=backend-breitling-league

echo -e "\n5. Test HTTP..."
curl -I https://backend-breitling-league-e1d83468309e.herokuapp.com/docs

echo -e "\n6. Test des autres routes Scribe..."
curl -I https://backend-breitling-league-e1d83468309e.herokuapp.com/docs.openapi
curl -I https://backend-breitling-league-e1d83468309e.herokuapp.com/docs.postman
