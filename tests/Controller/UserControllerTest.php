<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\UtilsTests\UtilsTests;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;
    private ?UserRepository $repositoryUser = null;
    private ?UrlGeneratorInterface $urlGenerator = null;

    public function setUp(): void
    {
        $this->client = static::createClient();

        /** @var EntityManagerInterface $manager */
        $manager = $this->client
            ->getContainer()
            ->get('doctrine');

        /** @var UserRepository $userRepository */
        $userRepository = $manager->getRepository(User::class);

        $this->repositoryUser = $userRepository;

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $this->client
            ->getContainer()
            ->get('router');

        $this->urlGenerator = $urlGenerator;
    }

    public function testAllRoutesUsersWithoutBeingAuthenticated(): void
    {
        /** @var KernelBrowser $client */
        $client = $this->client;

        // user_list
        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('user_list') ?? ''
        );

        $this->assertRedirectForGetAllRoutesUsersWithoutBeingAuthenticated();

        // user_create
        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('user_create') ?? ''
        );

        $this->assertRedirectForGetAllRoutesUsersWithoutBeingAuthenticated();

        // user_edit
        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('user_edit', ['id' => 1]) ?? ''
        );

        $this->assertRedirectForGetAllRoutesUsersWithoutBeingAuthenticated();
    }

    public function testAllRoutesUsersWithAuthenticatedWithoutRoleAdmin(): void
    {
        /** @var KernelBrowser $client */
        $client = $this->client;

        $client = UtilsTests::createAuthenticatedClient($client, 'user');

        // user_list
        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('user_list') ?? ''
        );

        $this->assertRedirectForGetAllRoutesUsersWithAuthenticatedWithoutRoleAdmin();

        // user_create
        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('user_create') ?? ''
        );

        $this->assertRedirectForGetAllRoutesUsersWithAuthenticatedWithoutRoleAdmin();

        // user_edit
        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('user_edit', ['id' => 1]) ?? ''
        );

        $this->assertRedirectForGetAllRoutesUsersWithAuthenticatedWithoutRoleAdmin();
    }

    public function testGetUserList(): void
    {
        $countUsers = count($this->repositoryUser?->findAll() ?? []);

        /** @var KernelBrowser $client */
        $client = $this->client;
        $client = UtilsTests::createAuthenticatedClient($client, 'admin');

        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('user_list') ?? ''
        );

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $crawler = $client->getCrawler();
        $countUsersDom = $crawler->filter('tbody tr th')->count();

        $this->assertEquals($countUsers, $countUsersDom);
    }

    public function testCreateUser(): void
    {
        $countUsers = count($this->repositoryUser?->findAll() ?? []);

        /** @var KernelBrowser $client */
        $client = $this->client;
        $this->client = UtilsTests::createAuthenticatedClient($client, 'admin');

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('user_list') ?? ''
        );

        $crawler = $this->client->getCrawler();
        $link = $crawler->selectLink('Créer un utilisateur')->link();

        $client->click($link);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/users/create', $this->client?->getRequest()->getPathInfo());

        // FAILED
        $this->failedSubmitFormUser();

        // SUCCESS
        /** @var Crawler $crawler */
        $crawler = $this->client?->getCrawler();

        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'test',
            'user[password][first]' => '123456',
            'user[password][second]' => '123456',
            'user[email]' => 'test@test.fr',
            'user[isAdmin]' => false
        ]);

        $this->submitFormAndFollowRedirect($form);

        $newUser = $this->repositoryUser?->findOneBy(['username' => 'test']);

        $countUsersAfterAddNewTask = count($this->repositoryUser?->findAll() ?? []);

        $this->assertInstanceOf(User::class, $newUser);
        $this->assertNotNull($newUser);
        $this->assertEquals($countUsers + 1, $countUsersAfterAddNewTask);

        // Anomaly correction => Choosing a role for a user (new feature)
        $this->assertNotContains("ROLE_ADMIN", $newUser->getRoles());
    }

    public function testUpdateUser(): void
    {
        $user = $this->repositoryUser?->findOneBy(['username' => 'test']);

        $idUser = $user?->getId() ?? -1;

        $this->client = $this->getClientAuthenticated('admin');

        $client = $this->client;

        // TEST NOT FOUND PAGE WITH ID NOT EXISTS

        $this->assertNotFoundPage(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('user_edit', ['id' => 20]) ?? ''
        );

        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('user_list') ?? ''
        );

        /** @var Crawler $crawler */
        $crawler = $client->getCrawler();
        $link = $crawler->filter('a[href="/users/' . $idUser . '/edit"]')->link();

        $client->click($link);

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals('/users/' . $idUser . '/edit', $client->getRequest()->getPathInfo());

        $crawler = $client->getCrawler();

        $form = $crawler->selectButton('Modifier')->form([
            'user[username]' => 'test',
            'user[password][first]' => '123456',
            'user[password][second]' => '123456',
            'user[email]' => 'edit-test@test.fr',
            'user[isAdmin]' => true
        ]);

        $this->submitFormAndFollowRedirect($form);

        $userUpdated = $this->repositoryUser?->findOneBy(['email' => 'edit-test@test.fr']);

        $this->assertInstanceOf(User::class, $userUpdated);
        $this->assertNotNull($userUpdated);
        $this->assertEquals('edit-test@test.fr', $userUpdated->getEmail());

        // Anomaly correction => Choosing a role for a user (new feature)
        $this->assertContains("ROLE_ADMIN", $userUpdated->getRoles());
    }

    private function failedSubmitFormUser(): void
    {
        $this->assertValidationFormCreateUser([
            'username' => ['value' => '', 'error' => 'Vous devez saisir un nom d\'utilisateur.'],
            'passwordFirst' => ['value' => '', 'error' => null],
            'passwordSecond' => ['value' => '', 'error' => null],
            'email' => ['value' => '', 'error' => 'Vous devez saisir une adresse email.'],
        ]);

        $this->assertValidationFormCreateUser([
            'username' => ['value' => 'user', 'error' => null],
            'passwordFirst' => ['value' => '123456', 'error' => 'Les deux mots de passe doivent correspondre.'],
            'passwordSecond' => ['value' => '1234567', 'error' => null],
            'email' => ['value' => 'test', 'error' => 'Le format de l\'adresse n\'est pas correcte.'],
        ]);

        $this->assertValidationFormCreateUser([
            'username' => ['value' => 'user', 'error' => 'This value is already used.'],
            'passwordFirst' => ['value' => '123456', 'error' => null],
            'passwordSecond' => ['value' => '123456', 'error' => null],
            'email' => ['value' => 'user@oc-p8.fr', 'error' => null],
        ]);

        $this->assertValidationFormCreateUser([
            'username' => ['value' => 'test', 'error' => null],
            'passwordFirst' => ['value' => '123456', 'error' => null],
            'passwordSecond' => ['value' => '123456', 'error' => null],
            'email' => ['value' => 'user@oc-p8.fr', 'error' => 'This value is already used.'],
        ]);
    }

    /**
     * @param array<string, array<string, string|null>> $values
     *
     * @return void
     */
    private function assertValidationFormCreateUser(array $values): void
    {
        /** @var Crawler $crawler */
        $crawler = $this->client?->getCrawler();

        // FAILED
        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => $values['username']['value'],
            'user[password][first]' => $values['passwordFirst']['value'],
            'user[password][second]' => $values['passwordSecond']['value'],
            'user[email]' => $values['email']['value']
        ]);

        $this->client?->submit($form);

        /** @var Crawler $crawler */
        $crawler = $this->client?->getCrawler();

        $errors = $crawler->filter('div.invalid-feedback');

        $indexNode = 0;
        foreach ($values as $value) {
            if ($value['error'] !== null) {
                $error = trim($errors->getNode($indexNode)?->textContent ?? '');
                $this->assertEquals($value['error'], $error);
                $indexNode++;
            }
        }
    }

    private function assertNotFoundPage(string $method, string $path): void
    {
        $this->client?->request($method, $path);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client?->getResponse()->getStatusCode());
    }

    private function assertRedirectForGetAllRoutesUsersWithoutBeingAuthenticated(): void
    {
        $this->assertEquals(Response::HTTP_FOUND, $this->client?->getResponse()->getStatusCode());

        $this->client?->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->client?->getResponse()->getStatusCode());
        $this->assertEquals('/login', $this->client?->getRequest()->getPathInfo());
    }

    private function assertRedirectForGetAllRoutesUsersWithAuthenticatedWithoutRoleAdmin(): void
    {
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client?->getResponse()->getStatusCode());
    }

    private function submitFormAndFollowRedirect(Form $form): void
    {
        $this->client?->submit($form);

        $this->assertEquals(Response::HTTP_FOUND, $this->client?->getResponse()->getStatusCode());

        $this->client?->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->client?->getResponse()->getStatusCode());
        $this->assertEquals('/users', $this->client?->getRequest()->getPathInfo());
    }

    private function getClientAuthenticated(string $username): KernelBrowser
    {
        /** @var KernelBrowser $client */
        $client = $this->client;
        return UtilsTests::createAuthenticatedClient(
            $client,
            $username
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $this->repositoryUser = null;
        $this->urlGenerator = null;
    }
}
