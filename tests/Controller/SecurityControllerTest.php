<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\UtilsTests\UtilsTests;

class SecurityControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;
    private ?User $user = null;

    public function setUp(): void
    {
        $this->client = static::createClient();

        /** @var EntityManagerInterface $manager */
        $manager = $this->client->getContainer()->get('doctrine');

        /** @var ?User $user */
        $user = $manager->getRepository(User::class)->findOneBy(['username' => 'user']);

        $this->user = $user;
    }

    public function testLoginSuccessful(): void
    {
        /** @var KernelBrowser $client */
        $client = $this->client;

        $client = UtilsTests::createAuthenticatedClient(
            $client,
            $this->user?->getUserIdentifier() ?? ''
        );

        $this->assertEquals(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode()
        );
        $this->assertEquals(
            '/',
            $client->getRequest()->getPathInfo()
        );

        $userAuthenticated = UtilsTests::getAuthenticatedUser($client);

        $this->assertInstanceOf(User::class, $userAuthenticated);
        $this->assertEquals($this->user, $userAuthenticated);
    }

    public function testLoginFailure(): void
    {
        /** @var KernelBrowser $client */
        $client = $this->client;

        $client = UtilsTests::createAuthenticatedClient($client, 'fail');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals('/login', $client->getRequest()->getPathInfo());

        $userAuthenticated = UtilsTests::getAuthenticatedUser($client);

        $this->assertNull($userAuthenticated);

        $crawler = $client->getCrawler();
        $error = $crawler->filter('div.alert.alert-danger')->text();
        $error = trim(preg_replace('/\s\s+/', ' ', $error) ?? '');

        $this->assertEquals('Invalid credentials.', $error);
    }

    public function testLogoutWithLink(): void
    {
        /** @var KernelBrowser $client */
        $client = $this->client;

        $client = UtilsTests::createAuthenticatedClient(
            $client,
            $this->user?->getUserIdentifier() ?? ''
        );

        $userAuthenticated = UtilsTests::getAuthenticatedUser($client);

        $this->assertEquals($this->user, $userAuthenticated);

        $crawler = $client->getCrawler();
        $link = $crawler->selectLink('Se déconnecter')->link();
        $client->click($link);
        $client->followRedirect();

        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        $client->followRedirect();

        $userAuthenticated = UtilsTests::getAuthenticatedUser($client);

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals('/login', $client->getRequest()->getPathInfo());
        $this->assertNull($userAuthenticated);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $this->user = null;
    }
}
