<?php

namespace App\Tests\Controller;

use App\Entity\User;
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

        $this->user = $this->client
            ->getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['username' => 'user']);
    }

    public function testLoginSuccessful(): void
    {
        $this->client = UtilsTests::createAuthenticatedClient($this->client, $this->user->getUserIdentifier());

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/', $this->client->getRequest()->getPathInfo());

        $userAuthenticated = UtilsTests::getAuthenticatedUser($this->client);

        $this->assertInstanceOf(User::class, $userAuthenticated);
        $this->assertEquals($this->user, $userAuthenticated);
    }

    public function testLoginFailure(): void
    {
        $this->client = UtilsTests::createAuthenticatedClient($this->client, 'fail');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/login', $this->client->getRequest()->getPathInfo());

        $userAuthenticated = UtilsTests::getAuthenticatedUser($this->client);

        $this->assertNull($userAuthenticated);

        $crawler = $this->client->getCrawler();
        $error = $crawler->filter('div.alert.alert-danger')->text();
        $error = trim(preg_replace('/\s\s+/', ' ', $error));

        $this->assertEquals('Invalid credentials.', $error);
    }

    public function testLogoutWithLink(): void
    {
        $this->client = UtilsTests::createAuthenticatedClient($this->client, $this->user->getUserIdentifier());

        $userAuthenticated = UtilsTests::getAuthenticatedUser($this->client);

        $this->assertEquals($this->user, $userAuthenticated);

        $crawler = $this->client->getCrawler();
        $link = $crawler->selectLink('Se déconnecter')->link();
        $this->client->click($link);
        $this->client->followRedirect();

        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        $this->client->followRedirect();

        $userAuthenticated = UtilsTests::getAuthenticatedUser($this->client);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/login', $this->client->getRequest()->getPathInfo());
        $this->assertNull($userAuthenticated);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $this->user = null;
    }
}
