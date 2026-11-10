<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Statuts métier d'un prospect dans le mini-CRM.
 */
enum StatutContact: string
{
    case NOUVEAU = 'nouveau';
    case CONTACTE = 'contacte';
    case RELANCE = 'relance';
    case GAGNE = 'gagne';
    case PERDU = 'perdu';

    /**
     * Libellé affichable dans l'interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::NOUVEAU => 'Nouveau',
            self::CONTACTE => 'Contacté',
            self::RELANCE => 'Relancé',
            self::GAGNE => 'Gagné',
            self::PERDU => 'Perdu',
        };
    }

    /**
     * Classe CSS utilisée pour le badge de statut.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::NOUVEAU => 'text-bg-secondary',
            self::CONTACTE => 'text-bg-info',
            self::RELANCE => 'text-bg-warning',
            self::GAGNE => 'text-bg-success',
            self::PERDU => 'text-bg-danger',
        };
    }

    /**
     * Liste des valeurs acceptées par les filtres et l'export.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $statut): string => $statut->value, self::cases());
    }
}
