<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\UtilsTests\UtilsTests;

class SecurityControllerTest extends WebTestCase
{
    private $client = null;
    private $user = null;
    private $urlGenerator = null;

    public function setUp()
    {
        $this->client = static::createClient();

        $this->user = $this->client
            ->getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['username' => 'user']);

        $this->urlGenerator = $this->client
            ->getContainer()
            ->get('router');
    }

    public function testLoginSuccessful()
    {
        $this->client = UtilsTests::createAuthenticatedClient($this->client, $this->user->getUsername());

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/', $this->client->getRequest()->getPathInfo());

        $userAuthenticated = $this->client
            ->getContainer()
            ->get('security.token_storage')
            ->getToken()
            ->getUser();

        $this->assertInstanceOf(User::class, $userAuthenticated);
        $this->assertEquals($this->user, $userAuthenticated);
    }

    public function testLoginFailure()
    {
        $this->client = UtilsTests::createAuthenticatedClient($this->client, 'fail');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/login', $this->client->getRequest()->getPathInfo());

        $userAuthenticated = UtilsTests::getAuthenticatedUser($this->client);

        $this->assertEquals('anon.', $userAuthenticated);

        $crawler = $this->client->getCrawler();
        $error = $crawler->filter('div.alert.alert-danger')->text();
        $error = trim(preg_replace('/\s\s+/', ' ', $error));

        $this->assertEquals('Invalid credentials.', $error);
    }

    public function testLogoutWithLink()
    {
        $this->client = UtilsTests::createAuthenticatedClient($this->client, $this->user->getUsername());

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
        $this->assertEquals('anon.', $userAuthenticated);

    }

    public function tearDown()
    {
        $this->client = null;
        $this->user = null;
        $this->urlGenerator = null;
    }
}
