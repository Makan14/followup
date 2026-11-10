<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Crée les tables métier du mini-CRM FollowUp.
 */
final class Version20261108161657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée les tables contact et utilisateur.';
    }

    public function up(Schema $schema): void
    {
        // Création des tables principales.
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(120) NOT NULL, email VARCHAR(180) NOT NULL, telephone VARCHAR(30) DEFAULT NULL, entreprise VARCHAR(120) DEFAULT NULL, statut VARCHAR(20) NOT NULL, date_prochaine_relance DATETIME DEFAULT NULL, notes LONGTEXT DEFAULT NULL, date_creation DATETIME NOT NULL, date_mise_ajour DATETIME DEFAULT NULL, INDEX idx_contact_statut (statut), INDEX idx_contact_relance (date_prochaine_relance), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX uniq_utilisateur_email (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // Retour arrière : suppression des tables métier.
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE utilisateur');
    }
}
