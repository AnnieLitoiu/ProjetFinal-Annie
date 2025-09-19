## Créez un projet squelette
```
symfony new --webapp Projet1Symfony
cd Projet1Symfony

: http://localhost:8000/ 
symfony open:local
```
## Serveur
```
symfony serve 
symfony server:start
symfony server:stop
```
## Route
```
#[Route("/exemple/affiche/vue1")] //exemple

Si vous avez une erreur de "Route not found" et vous êtes completement certaines que vos routes sont bonnes, videz la caché de Symfony et ré-essayez.
symfony console cache:clear --env dev

Vous pouvez afficher toutes les routes de votre projet en tapant dans la console cette ligne :
symfony console debug:route
```
## Après clonage d'un repo
```
composer install
(Si dépendences JS - npm install)
``` 
## Symfony. Installation de Doctrine. 
``` 
Après avoir configuré le fichier .env avec la connexion
Rajouter les packages pour l'ORM

symfony composer req symfony/orm-pack
symfony composer req symfony/maker-bundle --dev
``` 
## Créer un controller 
``` 
symfony console make:controller

``` 
# Lancer la création de la BD. 
``` 
Allumez le serveur de BD (MySQL dans notre cas) 

symfony console doctrine:database:create
``` 
# Création/update des entités
``` 
symfony console make:entity 
(valable pour créer une nouvelle ou rajouter de propriétés à une éxistante)

Si on édite le fichier de l'entité, à la main:
symfony console make:entity ---regenerate
``` 
# Créer une migration, la lancer
``` 
symfony console make:migration
symfony console doctrine:migrations:migrate
```
## GIT
```
git status
git add .
git commit -m "message du commit"
git push
git push --set-upstream -f origin main
git push -f

git pull
git push -u origin main
git remote -v
git init
git remote add origin http://repogit...  //rajouter un repo remote
git branch -M main
git commit...

git remote remove origin # effacer le lien avec le repo remote
git reset --hard
git reset --hard HEAD
git clean -df
git clean -f
git switch "branche"

```
## Fixtures
```
Installez le support pour les fixtures:
symfony composer req --dev orm-fixtures
composer require fakerphp/faker
symfony console make:fixture
symfony console doctrine:fixtures:load --append 
symfony console d:f:l 

```
## Formulaire
```
symfony composer req symfony/form
symfony console make:form

```
## Utilisateur/Login/Logout
```
symfony composer req symfony/security-bundle
symfony console make:user
symfony console make:security:form-login
composer require --dev orm-fixtures
symfony console security:hash-password
```
## databaseRestart.bat (fichier à ajouter) 
```
@echo off

echo Ce script va effacer la BD, la réinitialiser et la remplit avec des données de test (Fixtures)
symfony console doctrine:database:drop --force
symfony console doctrine:database:create

del migrations\Ve*
symfony console make:migration --no-interaction
symfony console doctrine:migrations:migrate --no-interaction
symfony console doctrine:fixtures:load --no-interaction


.\databaseRestart.bat

```