<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\StatutContact;
use App\Repository\ContactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Prospect suivi dans le mini-CRM.
 */
#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[ORM\Table(name: 'contact')]
#[ORM\Index(name: 'idx_contact_statut', columns: ['statut'])]
#[ORM\Index(name: 'idx_contact_relance', columns: ['date_prochaine_relance'])]
#[ORM\HasLifecycleCallbacks]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(min: 2, max: 120, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.', maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    private string $nom = '';

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email {{ value }} n'est pas valide.")]
    #[Assert\Length(max: 180)]
    private string $email = '';

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(max: 30, maxMessage: 'Le téléphone ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $telephone = null;

    #[ORM\Column(length: 120, nullable: true)]
    #[Assert\Length(max: 120, maxMessage: "L'entreprise ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $entreprise = null;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: StatutContact::class)]
    #[Assert\NotNull(message: 'Le statut est obligatoire.')]
    private StatutContact $statut = StatutContact::NOUVEAU;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateProchaineRelance = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 5000, maxMessage: 'Les notes ne peuvent pas dépasser {{ limit }} caractères.')]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $dateCreation;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateMiseAJour = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->dateMiseAJour = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = trim($nom);

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = mb_strtolower(trim($email));

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone !== null ? trim($telephone) : null;

        return $this;
    }

    public function getEntreprise(): ?string
    {
        return $this->entreprise;
    }

    public function setEntreprise(?string $entreprise): self
    {
        $this->entreprise = $entreprise !== null ? trim($entreprise) : null;

        return $this;
    }

    public function getStatut(): StatutContact
    {
        return $this->statut;
    }

    public function setStatut(StatutContact $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateProchaineRelance(): ?\DateTimeImmutable
    {
        return $this->dateProchaineRelance;
    }

    public function setDateProchaineRelance(?\DateTimeImmutable $dateProchaineRelance): self
    {
        $this->dateProchaineRelance = $dateProchaineRelance;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getDateCreation(): \DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function getDateMiseAJour(): ?\DateTimeImmutable
    {
        return $this->dateMiseAJour;
    }

    /**
     * Met à jour la date de modification avant chaque mise à jour Doctrine.
     */
    #[ORM\PreUpdate]
    public function mettreAJourDate(): void
    {
        $this->dateMiseAJour = new \DateTimeImmutable();
    }
}
