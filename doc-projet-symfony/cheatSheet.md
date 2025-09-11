## Serveur

```
symfony serve 
symfony server:start
symfony server:stop
```

## Après clonage d'un repo

```
composer install
(Si dépendences JS - npm install)
``` 

## GIT

```
git add .
git commit -m "message du commit"
git remote add origin http://repogit...  # rajouter un repo remote
git remote remove origin # effacer le lien avec le repo remote
```

## Symfony

Après avoir configuré le fichier .env avec la connexion

``` 
# Rajouter les packages pour l'ORM
symfony composer req symfony/orm-pack
symfony composer req symfony/maker-bundle --dev
``` 
``` 
# Lancer la création de la BD 
symfony console doctrine:database:create
``` 

# Création/update des entités

``` 
symfony console make:entity 
``` 

(valable pour créer une nouvelle ou rajouter de propriétés à une éxistante)

# Créer une migration, la lancer

``` 
symfony console make:migration
symfony console doctrine:migrations:migrate
```