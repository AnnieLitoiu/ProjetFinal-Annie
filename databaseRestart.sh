#!/bin/bash
# Ce script va effacer la BD, la réinitialiser et la remplir avec des données de test (Fixtures)

echo "Ce script va effacer la BD, la réinitialiser et la remplir avec des données de test (Fixtures)"

# Supprime et recrée la base de données
symfony console doctrine:database:drop --force
symfony console doctrine:database:create

# Supprime les fichiers de migration commençant par "Ve"
rm -f migrations/Ve*

# Crée une nouvelle migration
symfony console make:migration --no-interaction

# Exécute les migrations
symfony console doctrine:migrations:migrate --no-interaction

# Charge les fixtures
symfony console doctrine:fixtures:load --no-interaction
