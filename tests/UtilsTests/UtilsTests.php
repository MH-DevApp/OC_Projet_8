<?php

namespace App\Tests\UtilsTests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class UtilsTests
{
    public static function createAuthenticatedClient(KernelBrowser $client, string $username): KernelBrowser
    {
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form();
        $form['username'] = $username;
        $form['password'] = '123456';

        $client->submit($form);

        $client->followRedirect();

        return $client;
    }

    public static function getAuthenticatedUser(KernelBrowser $client): ?UserInterface
    {
        /** @var ?TokenStorageInterface $tokenStorage */
        $tokenStorage = $client->getContainer()->get('security.token_storage');
        return $tokenStorage?->getToken()?->getUser();
    }
}
