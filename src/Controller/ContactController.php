<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contact;
use App\Enum\StatutContact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * CRUD, recherche, filtres et export des contacts.
 */
#[Route('/contacts')]
class ContactController extends AbstractController
{
    private const TRIS_AUTORISES = ['date_creation', 'date_relance', 'nom'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContactRepository $contactRepository,
    ) {
    }

    #[Route('', name: 'app_contact_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $recherche = trim((string) $request->query->get('q', ''));
        $statut = (string) $request->query->get('statut', '');
        $tri = (string) $request->query->get('tri', 'date_creation');
        $sens = (string) $request->query->get('sens', 'desc');
        $page = max(1, (int) $request->query->get('page', '1'));

        if (!in_array($tri, self::TRIS_AUTORISES, true)) {
            $tri = 'date_creation';
        }
        $sens = $sens === 'asc' ? 'asc' : 'desc';

        if (!in_array($statut, StatutContact::values(), true)) {
            $statut = '';
        }

        $query = $this->contactRepository
            ->createFilteredQueryBuilder($recherche !== '' ? $recherche : null, $statut !== '' ? $statut : null, $tri, $sens)
            ->getQuery();

        $paginator = new Paginator($query);
        $total = count($paginator);
        $limite = (int) $this->getParameter('app.contacts_par_page');
        $pages = max(1, (int) ceil($total / $limite));
        $page = min($page, $pages);

        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $limite)
            ->setMaxResults($limite);

        return $this->render('contact/index.html.twig', [
            'contacts' => $paginator,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'recherche' => $recherche,
            'statut' => $statut,
            'tri' => $tri,
            'sens' => $sens,
            'statuts' => StatutContact::cases(),
            // Données de la colonne latérale (widgets de relances et de statuts).
            'counts' => $this->contactRepository->countByStatus(),
            'total_global' => $this->contactRepository->countTotal(),
            'relances_today' => $this->contactRepository->countRelancesToday(),
            'relances_late' => $this->contactRepository->countRelancesLate(),
        ]);
    }

    #[Route('/nouveau', name: 'app_contact_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le contact a été créé.');

            return $this->redirectToRoute('app_contact_show', ['id' => $contact->getId()]);
        }

        return $this->render('contact/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/export.csv', name: 'app_contact_export', methods: ['GET'])]
    public function export(Request $request): StreamedResponse
    {
        $recherche = trim((string) $request->query->get('q', ''));
        $statut = (string) $request->query->get('statut', '');
        $tri = (string) $request->query->get('tri', 'date_creation');
        $sens = (string) $request->query->get('sens', 'desc');

        if (!in_array($tri, self::TRIS_AUTORISES, true)) {
            $tri = 'date_creation';
        }
        $sens = $sens === 'asc' ? 'asc' : 'desc';
        if (!in_array($statut, StatutContact::values(), true)) {
            $statut = '';
        }

        $contacts = $this->contactRepository
            ->createFilteredQueryBuilder($recherche !== '' ? $recherche : null, $statut !== '' ? $statut : null, $tri, $sens)
            ->getQuery()
            ->getResult();

        $response = new StreamedResponse(function () use ($contacts): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // BOM UTF-8 pour améliorer l'ouverture dans Excel.
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['nom', 'email', 'telephone', 'entreprise', 'statut', 'date_prochaine_relance', 'notes', 'date_creation', 'date_mise_a_jour'], ';', '"', '\\');

            foreach ($contacts as $contact) {
                fputcsv($handle, [
                    $contact->getNom(),
                    $contact->getEmail(),
                    $contact->getTelephone() ?? '',
                    $contact->getEntreprise() ?? '',
                    $contact->getStatut()->label(),
                    $contact->getDateProchaineRelance()?->format('Y-m-d') ?? '',
                    $contact->getNotes() ?? '',
                    $contact->getDateCreation()->format('Y-m-d H:i'),
                    $contact->getDateMiseAJour()?->format('Y-m-d H:i') ?? '',
                ], ';', '"', '\\');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="contacts-' . date('Ymd-His') . '.csv"');

        return $response;
    }

    #[Route('/{id}', name: 'app_contact_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Contact $contact): Response
    {
        return $this->render('contact/show.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_contact_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Contact $contact): Response
    {
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Le contact a été modifié.');

            return $this->redirectToRoute('app_contact_show', ['id' => $contact->getId()]);
        }

        return $this->render('contact/edit.html.twig', [
            'contact' => $contact,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_contact_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Contact $contact): Response
    {
        $token = (string) $request->request->get('_token', '');

        if (!$this->isCsrfTokenValid('delete' . $contact->getId(), $token)) {
            $this->addFlash('error', 'Jeton de sécurité invalide. Suppression annulée.');

            return $this->redirectToRoute('app_contact_index');
        }

        $this->entityManager->remove($contact);
        $this->entityManager->flush();

        $this->addFlash('success', 'Le contact a été supprimé.');

        return $this->redirectToRoute('app_contact_index');
    }
}
