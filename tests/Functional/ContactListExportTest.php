<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\FunctionalTestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Vérifie recherche, filtres, dashboard et export CSV.
 */
final class ContactListExportTest extends FunctionalTestCase
{
    public function testRechercheEtFiltreStatut(): void
    {
        $this->loginAsAdmin();

        $this->client->request('GET', '/contacts', ['q' => 'Alpha', 'statut' => 'nouveau']);
        self::assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString('Camille Martin', $content);
        self::assertStringContainsString('Hugo Durand', $content);
        self::assertStringNotContainsString('Lucas Bernard', $content);

        $this->client->request('GET', '/contacts', ['statut' => 'gagne']);
        self::assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString('Nathan Robert', $content);
        self::assertStringContainsString('Louis Michel', $content);
        self::assertStringNotContainsString('Camille Martin', $content);
    }

    public function testDashboardAfficheLesCompteurs(): void
    {
        $this->loginAsAdmin();
        $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString('Total contacts', $content);
        self::assertStringContainsString('Relances aujourd', $content);
        self::assertStringContainsString('Relances en retard', $content);
    }

    public function testExportCsvFiltre(): void
    {
        $this->loginAsAdmin();
        $this->client->request('GET', '/contacts/export.csv', ['q' => 'Alpha']);
        self::assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        self::assertInstanceOf(StreamedResponse::class, $response);
        self::assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        self::assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));

        $callback = $response->getCallback();
        self::assertNotNull($callback);
        ob_start();
        $callback();
        $content = ob_get_clean();
        self::assertIsString($content);
        self::assertStringContainsString('nom;email', $content);
        self::assertStringContainsString('Camille Martin', $content);
        self::assertStringContainsString('Alpha Studio', $content);
        self::assertStringNotContainsString('Lucas Bernard', $content);
    }
}
