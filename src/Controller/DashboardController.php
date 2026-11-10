<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\StatutContact;
use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Tableau de bord principal.
 */
class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard', methods: ['GET'])]
    public function index(ContactRepository $contactRepository): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'total' => $contactRepository->countTotal(),
            'counts' => $contactRepository->countByStatus(),
            'relances_today' => $contactRepository->countRelancesToday(),
            'relances_late' => $contactRepository->countRelancesLate(),
            'recent_contacts' => $contactRepository->findRecent(3),
            'statuts' => StatutContact::cases(),
        ]);
    }
}
