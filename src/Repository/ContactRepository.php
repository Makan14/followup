<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contact;
use App\Enum\StatutContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Requêtes métier liées aux contacts.
 *
 * @extends ServiceEntityRepository<Contact>
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    /**
     * Construit la requête de liste avec recherche, filtre et tri.
     * Les valeurs de tri sont validées dans le contrôleur.
     */
    public function createFilteredQueryBuilder(?string $recherche, ?string $statut, string $tri, string $sens): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        if ($recherche !== null && trim($recherche) !== '') {
            $qb->andWhere('LOWER(c.nom) LIKE :recherche OR LOWER(c.email) LIKE :recherche OR LOWER(c.entreprise) LIKE :recherche')
                ->setParameter('recherche', '%' . mb_strtolower(trim($recherche)) . '%');
        }

        if ($statut !== null && $statut !== '') {
            $statutEnum = StatutContact::tryFrom($statut);
            if ($statutEnum !== null) {
                $qb->andWhere('c.statut = :statut')->setParameter('statut', $statutEnum);
            }
        }

        $colonnes = [
            'date_creation' => 'c.dateCreation',
            'date_relance' => 'c.dateProchaineRelance',
            'nom' => 'c.nom',
        ];

        $colonne = $colonnes[$tri] ?? 'c.dateCreation';
        $direction = $sens === 'asc' ? 'ASC' : 'DESC';

        return $qb->orderBy($colonne, $direction)->addOrderBy('c.id', 'DESC');
    }

    /**
     * Nombre total de contacts.
     */
    public function countTotal(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Nombre de contacts pour chaque statut.
     *
     * @return array<string, int>
     */
    public function countByStatus(): array
    {
        $counts = array_fill_keys(StatutContact::values(), 0);

        $rows = $this->createQueryBuilder('c')
            ->select('c.statut AS statut, COUNT(c.id) AS total')
            ->groupBy('c.statut')
            ->getQuery()
            ->getArrayResult();

        foreach ($rows as $row) {
            $statut = $row['statut'] instanceof StatutContact ? $row['statut']->value : (string) $row['statut'];
            if (array_key_exists($statut, $counts)) {
                $counts[$statut] = (int) $row['total'];
            }
        }

        return $counts;
    }

    /**
     * Relances prévues aujourd'hui.
     */
    public function countRelancesToday(): int
    {
        $debut = new \DateTimeImmutable('today');
        $fin = $debut->modify('+1 day');

        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateProchaineRelance >= :debut')
            ->andWhere('c.dateProchaineRelance < :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Relances dont la date est dépassée.
     */
    public function countRelancesLate(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateProchaineRelance < :today')
            ->setParameter('today', new \DateTimeImmutable('today'))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Derniers contacts créés pour le tableau de bord.
     *
     * @return list<Contact>
     */
    public function findRecent(int $limit = 5): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.dateCreation', 'DESC')
            ->addOrderBy('c.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
