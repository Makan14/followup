<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\FunctionalTestCase;

/**
 * Vérifie la protection des pages et la connexion.
 */
final class SecurityTest extends FunctionalTestCase
{
    public function testPagesMetierRedirigentVersConnexion(): void
    {
        $this->client->request('GET', '/contacts');
        self::assertResponseRedirects('/connexion', 302);

        $this->client->request('GET', '/');
        self::assertResponseRedirects('/connexion', 302);

        $this->client->request('GET', '/contacts/export.csv');
        self::assertResponseRedirects('/connexion', 302);
    }

    public function testConnexionReussie(): void
    {
        $crawler = $this->client->request('GET', '/connexion');
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = 'admin@followup.local';
        $form['_password'] = 'Demo1234!';

        $this->client->submit($form);
        self::assertResponseRedirects('/');

        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Tableau de bord', $this->client->getResponse()->getContent());
    }

    public function testConnexionRefuseeAvecMauvaisMotDePasse(): void
    {
        $crawler = $this->client->request('GET', '/connexion');
        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = 'admin@followup.local';
        $form['_password'] = 'mauvais';

        $this->client->submit($form);
        self::assertResponseRedirects('/connexion');

        $this->client->followRedirect();
        self::assertStringContainsString('Identifiants invalides', $this->client->getResponse()->getContent());
    }
}
