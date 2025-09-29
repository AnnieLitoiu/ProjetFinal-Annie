<?php

namespace App\DataFixtures;

use App\Entity\Quiz;
use App\Entity\Niveau;
use App\Entity\Question;
use App\Entity\Reponse;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker;

class QuizQuestionsReponsesFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        // Pools de questions par niveau : [enoncé, [rép1, rép2, rép3], indexBonneRéponse]
        $pools = [
            'debutant' => [
                ["Quelle balise HTML sert à créer un lien hypertexte ?", ["<a>", "<link>", "<href>"], 0],
                ["Quelle commande affiche une chaîne en PHP ?", ["echo", "print_r()", "console.log()"], 0],
                ["Quel code HTTP signifie 'OK' ?", ["404", "200", "500"], 1],
                ["Quel fichier gère les variables d'env dans Symfony en dev ?", [".env", "composer.json", "php.ini"], 0],
                ["Quel verbe HTTP est généralement utilisé pour créer une ressource ?", ["GET", "POST", "DELETE"], 1],
                ["Quel moteur de templates Symfony utilise-t-on par défaut ?", ["Twig", "Blade", "Mustache"], 0],
                ["Quelle commande installe les dépendances PHP ?", ["npm install", "composer install", "pip install"], 1],
                ["Quelle est la commande Git pour cloner un dépôt ?", ["git pull", "git clone", "git init"], 1],
                ["Quel type SQL retourne toutes les colonnes ?", ["SELECT *", "SELECT ALL", "SHOW *"], 0],
                ["Quel attribut Symfony déclare une route sur une méthode de contrôleur ?", ["#[Route(...)]", "@routing", "#route(...)"], 0],
                ["Quel format d’échange est textuel et clé/valeur ?", ["JSON", "PNG", "MP3"], 0],
                ["Quelle extension de fichier pour un template Twig ?", [".twig", ".php", ".html5"], 0],
                ["Quelle balise HTML définit le titre d’une page ?", ["<title>", "<head>", "<h1>"], 0],
                ["Quel sélecteur CSS cible une classe 'btn' ?", ["#btn", ".btn", "btn{}"], 1],
                ["Quelle commande affiche le statut Git ?", ["git status", "git diff", "git log --oneline"], 0],
                ["Quelle extension correspond à un fichier JSON ?", [".json", ".yaml", ".ini"], 0],
                ["Quel en-tête HTTP précise le type de contenu ?", ["Content-Type", "User-Agent", "Accept-Language"], 0],
                ["Quelle syntaxe Twig affiche une variable ?", ["{{ var }}", "{% var %}", "<%= var %>"], 0],
                ["Quelle commande lance un serveur Symfony local ?", ["symfony serve", "php artisan serve", "npm run dev"], 0],
                ["Comment créer un tableau en PHP ?", ["\$a = [];", "\$a = {};", "array() n’existe pas"], 0],
                ["Quel code HTTP signifie 'Non trouvé' ?", ["301", "403", "404"], 2],
                ["Quelle commande installe une lib PHP ?", ["composer require vendor/lib", "pip install lib", "npm i lib"], 0],
            ],
            'intermediaire' => [
                ["Quelle relation Doctrine modélise 'plusieurs Questions pour un Quiz' ?", ["ManyToMany", "ManyToOne", "OneToMany"], 2],
                ["Que fait l'autowiring dans Symfony ?", ["Configure le pare-feu", "Résout et injecte les dépendances", "Compile Twig en CSS"], 1],
                ["Différence principale entre 200 et 201 ?", ["201 = créé", "201 = redirection", "201 = erreur client"], 0],
                ["Quel JOIN retourne aussi les lignes sans correspondance à droite ?", ["INNER JOIN", "LEFT JOIN", "RIGHT JOIN"], 2],
                ["Avantage d’un index en base ?", ["Moins d’espace disque", "Requêtes plus rapides", "Transactions ACID"], 1],
                ["Que signifie 'idempotent' pour PUT ?", ["Même résultat sur réexécution", "Toujours crée", "Toujours supprime"], 0],
                ["Où déclare-t-on un service personnalisé ?", ["services.yaml", "routes.yaml", "messenger.yaml"], 0],
                ["Protection par défaut des formulaires Symfony ?", ["CSRF", "CSP", "CORS"], 0],
                ["Quel en-tête sert à la mise en cache conditionnelle ?", ["ETag", "Cookie", "Location"], 0],
                ["Quelle commande nettoie le cache Symfony ?", ["php bin/console cache:clear", "symfony cache:flush", "composer clear-cache"], 0],
                ["Quel fichier configure Doctrine (connexions/mappings) ?", ["doctrine.yaml", "security.yaml", "monolog.yaml"], 0],
                ["Quelle est la commande pour exécuter des migrations Doctrine ?", ["php bin/console doctrine:migrations:migrate", "composer migrate", "php bin/console db:update"], 0],
                ["Quelle clause SQL agrège des lignes par colonne ?", ["GROUP BY", "ORDER BY", "HAVING"], 0],
                ["Différence WHERE vs HAVING ?", ["Aucune", "HAVING après agrégation", "WHERE après agrégation"], 1],
                ["DELETE est-il idempotent selon HTTP ?", ["Oui", "Non", "Seulement avec ETag"], 0],
                ["Quel code pour 'ressource créée' + Location ?", ["201 Created", "202 Accepted", "204 No Content"], 0],
                ["En Doctrine, 'lazy loading' signifie…", ["Charge tout de suite", "Charge à la demande", "Ne charge jamais"], 1],
                ["Quel filtre Twig échappe le HTML ?", ["|raw", "|escape", "|nl2br"], 1],
                ["Quel fichier liste les dépendances Composer ?", ["composer.lock", "package.json", "pom.xml"], 0],
                ["Quelle commande génère une entité Doctrine ?", ["php bin/console make:entity", "doctrine:generate", "composer make:entity"], 0],
                ["Quel en-tête active le cache avec durée ?", ["Cache-Control: max-age=…", "Set-Cookie", "Authorization"], 0],
                ["301 vs 302 ?", ["301 = permanent, 302 = temporaire", "Inverse", "Deux redirections temporaires"], 0],
            ],
            'avance' => [
                ["Quel niveau d'isolation évite les lectures non répétables ?", ["READ COMMITTED", "REPEATABLE READ", "READ UNCOMMITTED"], 1],
                ["Symfony Messenger sert notamment à…", ["Gérer des files de messages/queues", "Compiler SCSS", "Servir des assets"], 0],
                ["Quelle stratégie sépare écriture/lecture de modèles ?", ["CQRS", "CRUD", "ORM"], 0],
                ["Quel code HTTP pour 'pas de contenu' ?", ["204", "202", "206"], 0],
                ["Que fait un 'Health check' de type Readiness en K8s ?", ["Vérifie prêt à recevoir du trafic", "Vérifie que le disque est plein", "Redémarre le pod"], 0],
                ["Avantage de Redis pour la session par rapport à la DB ?", ["Moins sûr", "Latence faible/mémoire", "Toujours persistant sur disque"], 1],
                ["Quel flux OAuth2 obtient un token côté back sécurisé ?", ["Implicit", "Authorization Code", "Password Grant public"], 1],
                ["Quel index accélère une recherche de préfixe texte ?", ["B-Tree", "Hash", "GIN/GIST selon moteur"], 2],
                ["Quel entête indique la politique CORS côté serveur ?", ["Access-Control-Allow-Origin", "X-Frame-Options", "Strict-Transport-Security"], 0],
                ["Pour limiter N+1 en Doctrine, on utilise ?", ["Eager loading avec join fetch", "Plus de transactions", "Triggers SQL"], 0],
                ["Quel pattern pour gérer des effets asynchrones testables ?", ["Event Sourcing", "Service Locator", "Active Record"], 0],
                ["Que signifie 429 Too Many Requests ?", ["Quota/débit dépassé", "Serveur indisponible", "Conflit de version"], 0],
                ["Quel niveau d’isolation évite les 'phantom reads' ?", ["REPEATABLE READ", "READ COMMITTED", "SERIALIZABLE"], 2],
                ["Différence Readiness vs Liveness probe (K8s) ?", ["Readiness=prêt au trafic, Liveness=vivant", "Liveness=prêt, Readiness=vivant", "Aucune"], 0],
                ["Quel pattern gère transactions distribuées sans 2PC ?", ["Saga", "Repository", "Builder"], 0],
                ["ETag fort vs faible (W/) ?", ["Fort compare octet à octet", "Faible = plus strict", "Aucune différence"], 0],
                ["PKCE améliore quel flux OAuth2 ?", ["Client Credentials", "Authorization Code public", "Implicit"], 1],
                ["PostgreSQL MVCC sert à…", ["Gérer versions concurrents sans locks longs", "Activer sharding", "Compresser tables"], 0],
                ["Index composite : règle la plus efficace ?", ["Colonne la plus sélective en premier", "Ordre alphabétique", "Toujours par clé étrangère"], 0],
                ["Quel code pour 'précondition échouée' (If-Match) ?", ["412", "428", "409"], 0],
                ["Quel header pour CORS avec credentials ?", ["Access-Control-Allow-Credentials: true", "X-Credential", "Allow-Credentials"], 0],
                ["Redis : politique d’éviction fréq. utilisée ?", ["noeviction", "volatile-lru / allkeys-lru", "fifo only"], 1],
            ],
        ];

        $niveauRefs = [
            'debutant' => 'niveau_debutant',
            'intermediaire' => 'niveau_intermediaire',
            'avance' => 'niveau_avance',
        ];

        foreach ($niveauRefs as $slug => $refName) {
            /** @var Niveau $niveau */
            $niveau = $this->getReference($refName, Niveau::class);

            // 15 quiz par niveau
            for ($qz = 1; $qz <= 15; $qz++) {
                $quiz = new Quiz();
                $themes = [
                    'Bases du Web',
                    'PHP & Symfony',
                    'HTTP & APIs',
                    'SQL & Doctrine',
                    'Bonne pratiques',
                    'Sécurité Web',
                    'Git & Versioning',
                    'Tests & Qualité (PHPUnit/Pest)',
                    'Front-end (HTML/CSS/JS)',
                    'Twig & Templating',
                    'Docker & Kubernetes',
                    'CI/CD',
                    'Caching & Performance',
                    'Cloud (AWS/Azure/GCP)',
                    'Messaging (RabbitMQ/Kafka)',
                    'Auth (OAuth2/OIDC/JWT)',
                    'Elasticsearch & Recherche',
                    'Linux & Réseau',
                    'Design Patterns & Architecture',
                    'Fichiers & Flux (CSV/JSON/Streams)',
                ];
                $quiz->setTitre(sprintf(
                    "Quiz %s #%d — %s",
                    ucfirst($slug === 'intermediaire' ? 'intermédiaire' : $slug),
                    $qz,
                    $themes[($qz - 1) % count($themes)]
                ));
                $quiz->setNiveau($niveau);

                // tirer 15 questions du pool du niveau (sans répétition si possible)
                $pool = $pools[$slug];
                // mélanger pour varier entre les 15 quiz
                shuffle($pool);
                $selected = array_slice($pool, 0, 15);

                foreach ($selected as $idx => [$enonce, $reps, $correctIndex]) {
                    $question = new Question();
                    $question->setEnonce($enonce);
                    $question->setQuiz($quiz);

                    // 3 réponses dont 1 correcte (index $correctIndex)
                    foreach ($reps as $i => $texteRep) {
                        $reponse = new Reponse();
                        $reponse->setTexte($texteRep);
                        $reponse->setEstCorrecte($i === $correctIndex);
                        $reponse->setQuestion($question);
                        $manager->persist($reponse);
                    }

                    $quiz->addQuestion($question);
                    $manager->persist($question);
                }

                $manager->persist($quiz);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [NiveauFixtures::class];
    }
}
