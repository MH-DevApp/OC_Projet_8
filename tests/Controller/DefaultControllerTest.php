<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Tests\UtilsTests\UtilsTests;

class DefaultControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;

    private ?UrlGeneratorInterface $urlGenerator = null;

    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->urlGenerator = $this->client
            ->getContainer()
            ->get('router');
    }

    public function testHomePageWithoutBeingAuthenticated(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('homepage')
        );

        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        $this->client->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/login', $this->client->getRequest()->getPathInfo());
    }

    public function testHomePageWhileBeingAuthenticated(): void
    {
        $this->client = UtilsTests::createAuthenticatedClient($this->client, 'user');
        $crawler = $this->client->getCrawler();

        $info = $crawler->filter('h1')->text();
        $info = trim(preg_replace('/\s\s+/', ' ', $info));

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(
            'Bienvenue sur Todo List, l\'application vous permettant de gérer l\'ensemble de vos tâches sans effort !',
            $info
        );
    }

}
