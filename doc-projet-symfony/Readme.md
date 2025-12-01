# Application de Quiz

## Description
Cette application web permet aux utilisateurs de tester leurs connaissances à travers des questionnaires classés par niveaux de difficulté. Elle est conçue pour être simple d'utilisation tout en offrant une expérience complète de quiz.

## Fonctionnalités

### Pour les utilisateurs
- Choix parmi différents niveaux de difficulté (Débutant, Intermédiaire, Avancé)
- Affichage des résultats avec correction
- Suivi du temps pour chaque tentative
- Score final avec détail des bonnes et mauvaises réponses

### Pour les administrateurs
- Gestion complète des quiz
- Création et édition des questions et réponses
- Gestion des niveaux de difficulté
- Suivi des performances des utilisateurs

## Structure des données

### Relations principales
- Un candidat peut effectuer plusieurs tentatives
- Une tentative est toujours liée à un seul candidat et à un seul quiz
- Un quiz peut être tenté par plusieurs candidats
- Un quiz appartient à un niveau
- Un niveau peut contenir plusieurs quiz
- Un quiz est composé de plusieurs questions
- Une question est toujours liée à un seul quiz
- Une question est composée de plusieurs propositions
- Une proposition est toujours liée à une seule question

## Niveaux de difficulté
1. Débutant : Questions basiques pour les nouveaux utilisateurs
2. Intermédiaire : Questions plus complexes nécessitant une certaine connaissance
3. Avancé : Questions expertes pour les utilisateurs expérimentés

## Règles de notation
- Chaque bonne réponse rapporte des points
- Le score est exprimé en pourcentage
- Le temps de réponse peut influencer le score selon le niveau

## Sécurité
- Authentification sécurisée
- Protection contre les attaques CSRF
- Validation des entrées utilisateur
- Gestion des sessions sécurisées

## Installation
1. Cloner le dépôt
2. Installer les dépendances : `composer install`
3. Configurer la base de données dans `.env.local`
4. Créer la base de données : `php bin/console doctrine:database:create`
5. Exécuter les migrations : `php bin/console doctrine:migrations:migrate`
6. Lancer le serveur : `symfony serve`

## Scripts utiles
- Redémarrer la base de données : `./databaseRestart.sh` (Linux/Mac) ou `databaseRestart.bat` (Windows)
