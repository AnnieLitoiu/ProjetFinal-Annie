@echo off
echo Ce script va effacer la BD, la réinitialiser et la remplit avec des données de test (Fixtures)
symfony console doctrine:database:drop --force
symfony console doctrine:database:create

del migrations\Ve*
symfony console make:migration --no-interaction
symfony console doctrine:migrations:migrate --no-interaction
symfony console doctrine:fixtures:load --no-interaction