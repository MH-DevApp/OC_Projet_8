<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\UtilsTests\UtilsTests;

class DefaultControllerTest extends WebTestCase
{
    private $client = null;

    private $urlGenerator = null;

    public function setUp()
    {
        $this->client = static::createClient();

        $this->urlGenerator = $this->client
            ->getContainer()
            ->get('router');
    }

    public function testHomePageWithoutBeingAuthenticated()
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

    public function testHomePageWhileBeingAuthenticated()
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
