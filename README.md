# FollowUp — Mini-CRM de suivi de prospects

FollowUp est une application web Symfony de démonstration permettant de suivre des prospects, leurs statuts et leurs relances.

Le projet est pensé comme un projet de portfolio recrutement : il montre une application métier complète, mais reste assez petit pour être compris, installé et expliqué rapidement.

## Objectif

Gérer des contacts commerciaux fictifs :

- ajouter, modifier, supprimer un contact ;
- suivre son statut : nouveau, contacté, relancé, gagné, perdu ;
- planifier une prochaine relance ;
- rechercher et filtrer les contacts ;
- visualiser les indicateurs clés dans un tableau de bord ;
- exporter la liste filtrée en CSV.

## Compétences démontrées

- Symfony 7.4
- PHP 8.2+
- Doctrine ORM et migrations
- Architecture MVC
- CRUD complet
- Authentification Symfony Security
- Sécurité : CSRF, mots de passe hachés, accès protégés, validation
- Recherche, filtres, tri et pagination
- Export CSV
- Tests fonctionnels PHPUnit
- Interface responsive avec Bootstrap 5
- Thème sombre / clair
- Documentation et préparation au déploiement

## Stack technique

| Élément | Choix |
|---|---|
| Langage | PHP 8.2+ |
| Framework | Symfony 7.4 |
| ORM | Doctrine ORM 3 |
| Base de données | MySQL ou MariaDB |
| Templates | Twig |
| CSS | Bootstrap 5 local + design personnalisé (`app.css`) |
| JavaScript | Vanilla JS minimal |
| Tests | PHPUnit 11 |
| Données de démonstration | Doctrine Fixtures |

Aucun build frontend complexe n’est nécessaire. Bootstrap est inclus localement dans `public/assets/`.

## Fonctionnalités

### Authentification

- Page de connexion : `/connexion`
- Déconnexion : `/deconnexion`
- Toutes les pages métier nécessitent un compte connecté.
- Compte administrateur de démonstration créé par fixtures.
- Mots de passe hachés.
- Protection CSRF sur la connexion et les suppressions.

### Contacts

Chaque contact contient :

- nom ;
- email ;
- téléphone ;
- entreprise ;
- statut ;
- date de prochaine relance ;
- notes ;
- date de création ;
- date de mise à jour.

Statuts disponibles :

- `nouveau`
- `contacte`
- `relance`
- `gagne`
- `perdu`

### Recherche, filtres et tri

- Recherche par nom, email ou entreprise.
- Filtre par statut.
- Tri par date de création, date de relance ou nom.
- Sens croissant ou décroissant.
- Pagination simple.

### Tableau de bord

Le tableau de bord affiche :

- nombre total de contacts ;
- nombre de contacts par statut ;
- relances prévues aujourd’hui ;
- relances en retard ;
- derniers contacts créés.

### Export CSV

- Export de la liste filtrée.
- Séparateur `;` pratique pour Excel en français.
- BOM UTF-8 pour les accents.
- Export protégé par authentification.

## Compte de démonstration

Après chargement des fixtures :

```text
Email : admin@followup.local
Mot de passe : Demo1234!
```

Ces identifiants sont uniquement destinés à la démonstration locale. Ne les utilisez pas en production.

## Installation sur Windows avec XAMPP

### Prérequis

- Windows 11
- XAMPP avec PHP 8.2+
- Composer
- MySQL ou MariaDB démarré depuis XAMPP
- Extensions PHP usuelles Symfony : `ctype`, `iconv`, `mbstring`, `pdo_mysql`, `intl`, `xml`, `curl`, `zip`

Si la commande `php` n’est pas reconnue, utilisez le chemin complet :

```text
C:\xampp\php\php.exe
```

### 1. Placer le projet

Exemple :

```text
C:\xampp\htdocs\followup
```

Ouvrez PowerShell dans ce dossier :

```powershell
cd C:\xampp\htdocs\followup
```

### 2. Installer les dépendances PHP

```powershell
composer install
```

### 3. Créer le fichier de configuration locale

```powershell
Copy-Item .env.local.example .env.local
```

Ouvrez le fichier :

```powershell
notepad .env.local
```

Adaptez au minimum :

```text
APP_SECRET=une_longue_valeur_aleatoire
DATABASE_URL="mysql://root:@127.0.0.1:3306/followup?serverVersion=mariadb-10.4.32&charset=utf8mb4"
```

Avec XAMPP par défaut, l’utilisateur MySQL est souvent `root` sans mot de passe. Si vous avez un mot de passe :

```text
DATABASE_URL="mysql://root:votre_mot_de_passe@127.0.0.1:3306/followup?serverVersion=mariadb-10.4.32&charset=utf8mb4"
```

Si vous utilisez MySQL 8, adaptez `serverVersion`, par exemple :

```text
DATABASE_URL="mysql://root:@127.0.0.1:3306/followup?serverVersion=8.0.32&charset=utf8mb4"
```

Ne commitez jamais `.env.local`.

### 4. Créer la base de données

```powershell
php bin/console doctrine:database:create --if-not-exists
```

### 5. Créer les tables

```powershell
php bin/console doctrine:migrations:migrate --no-interaction
```

### 6. Charger les données de démonstration

Attention : cette commande purge les données existantes de la base configurée.

```powershell
php bin/console doctrine:fixtures:load --no-interaction
```

### 7. Lancer le serveur local

```powershell
php -S localhost:8000 -t public public/index.php
```

Puis ouvrez :

```text
http://localhost:8000/connexion
```

## Tests

Créer la base de test :

```powershell
php bin/console doctrine:database:create --env=test --if-not-exists
```

Créer les tables de test :

```powershell
php bin/console doctrine:migrations:migrate --env=test --no-interaction
```

Lancer les tests :

```powershell
php bin/phpunit
```

Les tests couvrent :

- redirection vers la connexion si non connecté ;
- connexion réussie ;
- connexion refusée ;
- création d’un contact ;
- modification d’un contact ;
- suppression d’un contact ;
- refus de suppression sans CSRF valide ;
- validation du formulaire ;
- recherche et filtre ;
- tableau de bord ;
- export CSV.

## Vérifications utiles

```powershell
php bin/console lint:yaml config --parse-tags
```

```powershell
php bin/console lint:twig templates
```

```powershell
php bin/console lint:container
```

```powershell
php bin/console doctrine:schema:validate
```

```powershell
composer audit
```

## Routes principales

| Route | Rôle |
|---|---|
| `/connexion` | Connexion |
| `/deconnexion` | Déconnexion |
| `/` | Tableau de bord |
| `/contacts` | Liste, recherche, filtres |
| `/contacts/nouveau` | Création |
| `/contacts/{id}` | Détail |
| `/contacts/{id}/modifier` | Modification |
| `/contacts/{id}/supprimer` | Suppression POST avec CSRF |
| `/contacts/export.csv` | Export CSV filtré |

## Structure du projet

```text
followup/
├── config/                 Configuration Symfony
├── migrations/             Migration Doctrine
├── public/                 Point d'entrée web et assets locaux
├── src/
│   ├── Controller/         Contrôleurs MVC
│   ├── Entity/             Entités Doctrine
│   ├── Enum/               Statuts métier
│   ├── Form/               Formulaire Contact
│   ├── Repository/         Requêtes métier
│   └── DataFixtures/       Données de démonstration
├── templates/              Vues Twig
│   ├── partials/           Rail de navigation et colonne de widgets
│   └── contact/            Liste, fiche et formulaire
├── tests/                  Tests fonctionnels
├── translations/           Messages de sécurité en français
├── docs/
│   ├── screenshots/        Captures d'écran réelles
│   └── VERIFICATIONS.md    Journal des vérifications
├── .env.local.example      Modèle de configuration locale
└── README.md
```

## Sécurité

- Les pages métier sont protégées par `access_control`.
- Les mots de passe sont hachés via Symfony PasswordHasher.
- Les formulaires utilisent CSRF.
- Les suppressions utilisent un jeton CSRF dédié.
- Les entrées sont validées côté serveur.
- Twig échappe les sorties par défaut.
- `.env.local` est ignoré par Git.
- Aucun secret réel ne doit être commité.

## Déploiement rapide

Pour un hébergement PHP/MySQL classique :

1. Configurer les variables d’environnement de production, notamment `APP_ENV=prod`, `APP_SECRET` et `DATABASE_URL`.
2. Installer les dépendances sans les outils de développement :

```bash
composer install --no-dev --optimize-autoloader
```

3. Jouer les migrations :

```bash
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

4. Vider le cache :

```bash
php bin/console cache:clear --env=prod --no-debug
```

5. Pointer le serveur web vers le dossier `public/`.

Ne chargez pas les fixtures de démonstration en production.

## Captures d’écran

Les captures réelles du projet se trouvent dans `docs/screenshots/` :

```text
docs/screenshots/connexion-sombre.png
docs/screenshots/dashboard-sombre.png
docs/screenshots/dashboard-clair.png
docs/screenshots/contacts-sombre.png
docs/screenshots/formulaire-clair.png
docs/screenshots/fiche-clair.png
docs/screenshots/mobile-dashboard.png
```

## Interface et design

L’interface reprend le parti pris visuel du design fourni :

- cadre applicatif arrondi posé sur un fond neutre ;
- rail vertical d’icônes à gauche, avec pastille de marque et indicateur d’onglet actif ;
- barre supérieure avec recherche globale, icônes d’action et compte utilisateur ;
- bandeau de mise en avant dégradé, accents bleu et corail ;
- cartes de contacts avec vignette colorée selon le statut ;
- colonne latérale de widgets : relances, filtres par statut, bloc d’export ;
- tableaux et formulaires sobres, coins arrondis, ombres douces ;
- thème sombre par défaut et thème clair équivalent, mémorisés dans le navigateur ;
- adaptation mobile : le rail passe à l’horizontale et les grilles se replient.

Toutes les couleurs sont centralisées dans des variables `:root` et des blocs
`[data-bs-theme="dark"]` / `[data-bs-theme="light"]` dans `public/assets/css/app.css`.

## Pistes d’amélioration

- Inscription publique et rôles recruteur / admin.
- Historique des échanges par contact.
- Import CSV.
- Envoi d’emails de relance.
- API REST avec API Platform.
- Graphiques de conversion.
- Docker et CI GitHub Actions.
- Déploiement en ligne avec HTTPS.

## Licence

Projet de démonstration fourni tel quel pour apprentissage et portfolio. Bootstrap 5 est distribué sous licence MIT.
