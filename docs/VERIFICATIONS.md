# Vérifications effectuées

Projet vérifié dans l'environnement de préparation avec PHP 8.4 et une plateforme Composer verrouillée sur PHP 8.2.12.

## Résultat

- Composer validate : OK
- Composer audit : aucun avis de sécurité connu
- Lint PHP : OK
- Lint YAML : OK
- Lint Twig : OK
- Lint container : OK
- Migration Doctrine : OK
- Doctrine schema validate : mapping et schéma synchronisés
- Fixtures : 1 admin et 10 contacts fictifs
- Tests fonctionnels : 11 tests, 68 assertions, OK

## Scénarios testés

- Accès refusé aux pages métier sans connexion.
- Connexion réussie avec le compte de démonstration.
- Connexion refusée avec un mauvais mot de passe.
- Création d'un contact.
- Modification d'un contact.
- Suppression d'un contact avec CSRF valide.
- Refus de suppression avec CSRF invalide.
- Validation serveur du formulaire contact.
- Recherche et filtre par statut.
- Affichage du tableau de bord.
- Export CSV filtré protégé par authentification.

## À vérifier sur votre machine Windows

- `composer install`
- création de la base `followup`
- migrations
- fixtures
- connexion avec `admin@followup.local` / `Demo1234!`
- CRUD contact
- export CSV
- tests PHPUnit

## Vérifications complémentaires

- Installation `composer install --no-dev` : OK.
- Cache Symfony en environnement `prod` : OK.
- `lint:container --env=prod` : OK.
- `doctrine:schema:validate --env=prod` : OK.
- Smoke test HTTP : connexion, dashboard, liste contacts, formulaire contact, export CSV, assets Bootstrap : OK.
- Création d'un contact via HTTP avec CSRF : OK.

## Refonte visuelle (design fourni par le client)

- Gabarit retravaillé : cadre arrondi, rail d'icônes, barre supérieure avec recherche globale.
- Widgets de colonne latérale : relances, filtres par statut, bloc d'export.
- Cartes de contacts et bandeau de mise en avant avec accents bleu et corail.
- Thème sombre et thème clair vérifiés dans un navigateur Chromium (Playwright).
- Vérification mobile 390 x 844 px.
- Correctif `public/index.php` : le serveur intégré `php -S` sert désormais réellement
  les fichiers de `public/` (CSS, JS) au lieu de les passer à Symfony.
- Tests fonctionnels après refonte : 11 tests, 68 assertions, OK.
