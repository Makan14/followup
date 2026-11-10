<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Contact;
use App\Entity\User;
use App\Enum\StatutContact;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Données de démonstration : un admin et dix contacts fictifs.
 */
class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@followup.local');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Demo1234!'));
        $manager->persist($admin);

        $contacts = [
            ['Camille Martin', 'camille.martin@example.com', '0102030405', 'Alpha Studio', StatutContact::NOUVEAU, '+2 days', 'Premier contact via le site web.'],
            ['Lucas Bernard', 'lucas.bernard@example.com', '0102030406', 'Nova Conseil', StatutContact::CONTACTE, '+1 day', 'Appel de qualification prévu.'],
            ['Sofia Petit', 'sofia.petit@example.com', '0102030407', 'Atelier Lumen', StatutContact::RELANCE, 'today', 'Relancer après envoi de la proposition.'],
            ['Nathan Robert', 'nathan.robert@example.com', '0102030408', 'Bleu Horizon', StatutContact::GAGNE, '+10 days', 'Devis signé, démarrage du projet.'],
            ['Emma Richard', 'emma.richard@example.com', '0102030409', 'PixelForge', StatutContact::PERDU, null, 'Budget insuffisant pour le moment.'],
            ['Hugo Durand', 'hugo.durand@example.com', '0102030410', 'Alpha Studio', StatutContact::NOUVEAU, '-1 day', 'Demande de documentation technique.'],
            ['Léa Moreau', 'lea.moreau@example.com', '0102030411', 'Cactus Web', StatutContact::CONTACTE, '+5 days', 'Intéressée par une refonte Symfony.'],
            ['Gabriel Laurent', 'gabriel.laurent@example.com', '0102030412', 'Orbite Digitale', StatutContact::RELANCE, '-3 days', 'Sans réponse depuis le dernier email.'],
            ['Chloé Simon', 'chloe.simon@example.com', '0102030413', 'Maison Verre', StatutContact::NOUVEAU, '+7 days', 'Prospect rencontré en salon professionnel.'],
            ['Louis Michel', 'louis.michel@example.com', '0102030414', 'Seconde Peau', StatutContact::GAGNE, '+15 days', 'Maintenance annuelle acceptée.'],
        ];

        foreach ($contacts as [$nom, $email, $telephone, $entreprise, $statut, $relance, $notes]) {
            $contact = new Contact();
            $contact->setNom($nom);
            $contact->setEmail($email);
            $contact->setTelephone($telephone);
            $contact->setEntreprise($entreprise);
            $contact->setStatut($statut);
            $contact->setNotes($notes);
            if ($relance !== null) {
                $contact->setDateProchaineRelance(new \DateTimeImmutable($relance));
            }
            $manager->persist($contact);
        }

        $manager->flush();
    }
}
