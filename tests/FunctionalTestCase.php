<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Base des tests fonctionnels : client web et données de démonstration isolées.
 */
abstract class FunctionalTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->reloadFixtures();
    }

    /**
     * Recharge les fixtures avant chaque test pour éviter les effets de bord.
     */
    protected function reloadFixtures(): void
    {
        $container = static::getContainer();
        $entityManager = $this->entityManager();

        $loader = new Loader();
        $loader->addFixture(new AppFixtures($container->get(UserPasswordHasherInterface::class)));

        $executor = new ORMExecutor($entityManager, new ORMPurger($entityManager));
        $executor->execute($loader->getFixtures());
        $entityManager->clear();
    }

    protected function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine')->getManager();
    }

    /**
     * Connecte le compte admin créé par les fixtures.
     */
    protected function loginAsAdmin(): void
    {
        $user = $this->entityManager()->getRepository(User::class)->findOneBy(['email' => 'admin@followup.local']);
        self::assertNotNull($user, 'Le compte admin de démonstration doit exister.');

        $this->client->loginUser($user);
    }
}
