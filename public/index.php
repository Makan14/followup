<?php

use App\Kernel;

// Le serveur intégré de PHP ne sert pas les fichiers existants quand un
// routeur est fourni en ligne de commande : on les renvoie ici directement.
// Les fichiers inexistants continuent vers Symfony (gestion des 404).
if (PHP_SAPI === 'cli-server') {
    $fichier = __DIR__ . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_file($fichier)) {
        return false;
    }
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return static function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
