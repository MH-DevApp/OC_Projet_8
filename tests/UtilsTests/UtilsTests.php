<?php

namespace App\Tests\UtilsTests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

abstract class UtilsTests
{
    public static function createAuthenticatedClient(KernelBrowser $client, $username): KernelBrowser
    {
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form();
        $form['username'] = $username;
        $form['password'] = '123456';

        $client->submit($form);

        $client->followRedirect();

        return $client;
    }

    public static function getAuthenticatedUser(KernelBrowser $client): ?User
    {
        $token = $client->getContainer()->get('security.token_storage')->getToken();
        return $token ? $token->getUser() : null;
    }
}
