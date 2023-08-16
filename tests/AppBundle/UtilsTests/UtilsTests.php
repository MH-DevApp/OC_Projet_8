<?php

namespace Tests\AppBundle\UtilsTests;

use Symfony\Bundle\FrameworkBundle\Client;

abstract class UtilsTests
{
    public static function createAuthenticatedClient(Client $client, $username)
    {
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form();
        $form['_username'] = $username;
        $form['_password'] = '123456';

        $client->submit($form);

        $client->followRedirect();

        return $client;
    }

    public static function getAuthenticatedUser(Client $client)
    {
        return $client
            ->getContainer()
            ->get('security.token_storage')
            ->getToken()
            ->getUser();
    }
}
