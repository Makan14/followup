<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Contact;
use App\Tests\FunctionalTestCase;

/**
 * Vérifie le CRUD contact et la protection CSRF.
 */
final class ContactCrudTest extends FunctionalTestCase
{
    public function testCreationContact(): void
    {
        $this->loginAsAdmin();
        $nom = 'Contact Test ' . uniqid();
        $email = 'test.' . uniqid() . '@example.com';

        $crawler = $this->client->request('GET', '/contacts/nouveau');
        $form = $crawler->selectButton('Enregistrer')->form();
        $form['contact[nom]'] = $nom;
        $form['contact[email]'] = $email;
        $form['contact[telephone]'] = '0600000000';
        $form['contact[entreprise]'] = 'Entreprise Test';
        $form['contact[statut]'] = 'contacte';
        $form['contact[dateProchaineRelance]'] = '2026-12-01';
        $form['contact[notes]'] = 'Créé par un test fonctionnel.';

        $this->client->submit($form);
        self::assertResponseRedirects();

        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString($nom, $this->client->getResponse()->getContent());

        $contact = $this->entityManager()->getRepository(Contact::class)->findOneBy(['email' => $email]);
        self::assertNotNull($contact);
        self::assertSame($nom, $contact->getNom());
    }

    public function testModificationContact(): void
    {
        $this->loginAsAdmin();
        $contact = $this->entityManager()->getRepository(Contact::class)->findOneBy(['email' => 'camille.martin@example.com']);
        self::assertNotNull($contact);
        $id = $contact->getId();

        $crawler = $this->client->request('GET', '/contacts/' . $id . '/modifier');
        $form = $crawler->selectButton('Enregistrer')->form();
        $form['contact[nom]'] = 'Camille Martin Modifiée';

        $this->client->submit($form);
        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();

        $this->entityManager()->clear();
        $updated = $this->entityManager()->getRepository(Contact::class)->find($id);
        self::assertNotNull($updated);
        self::assertSame('Camille Martin Modifiée', $updated->getNom());
    }

    public function testSuppressionContact(): void
    {
        $this->loginAsAdmin();
        $contact = $this->entityManager()->getRepository(Contact::class)->findOneBy(['email' => 'hugo.durand@example.com']);
        self::assertNotNull($contact);
        $id = $contact->getId();

        $crawler = $this->client->request('GET', '/contacts/' . $id);
        $token = $crawler->filter('input[name="_token"]')->attr('value');
        $this->client->request('POST', '/contacts/' . $id . '/supprimer', ['_token' => $token]);
        self::assertResponseRedirects('/contacts');

        $this->client->followRedirect();
        self::assertResponseIsSuccessful();

        $this->entityManager()->clear();
        self::assertNull($this->entityManager()->getRepository(Contact::class)->find($id));
    }

    public function testSuppressionRefuseeSansCsrfValide(): void
    {
        $this->loginAsAdmin();
        $contact = $this->entityManager()->getRepository(Contact::class)->findOneBy(['email' => 'sofia.petit@example.com']);
        self::assertNotNull($contact);
        $id = $contact->getId();

        $this->client->request('POST', '/contacts/' . $id . '/supprimer', ['_token' => 'invalide']);
        self::assertResponseRedirects('/contacts');
        $this->client->followRedirect();

        $this->entityManager()->clear();
        self::assertNotNull($this->entityManager()->getRepository(Contact::class)->find($id));
    }

    public function testValidationCreation(): void
    {
        $this->loginAsAdmin();
        $crawler = $this->client->request('GET', '/contacts/nouveau');
        $form = $crawler->selectButton('Enregistrer')->form();
        $form['contact[nom]'] = '';
        $form['contact[email]'] = 'email-invalide';

        $this->client->submit($form);
        self::assertResponseStatusCodeSame(422);

        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString('Le nom est obligatoire', $content);
        self::assertStringContainsString('pas valide', $content);
    }
}
