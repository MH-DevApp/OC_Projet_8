<?php

namespace App\Tests\Controller;

use App\Repository\TaskRepository;
use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Tests\UtilsTests\UtilsTests;

class TaskControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;
    private ?TaskRepository $repositoryTask = null;
    private ?Task $task = null;
    private ?User $user = null;
    private ?UrlGeneratorInterface $urlGenerator = null;

    public function setUp(): void
    {
        $this->client = static::createClient();

        $doctrine = $this->client
            ->getContainer()
            ->get('doctrine');

        $this->user = $doctrine
            ->getRepository(User::class)
            ->findOneBy(['username' => 'user']);

        $this->repositoryTask = $doctrine
            ->getRepository(Task::class);

        $this->urlGenerator = $this->client
            ->getContainer()
            ->get('router');
    }

    public function testAllRoutesTasksWithoutBeingAuthenticated(): void
    {
        // task_list
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_list')
        );

        $this->assertRedirectForGetAllRoutesTasksWithoutBeingAuthenticated();

        // task_create
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_create')
        );

        $this->assertRedirectForGetAllRoutesTasksWithoutBeingAuthenticated();

        // task_edit
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_edit', ['id' => 1])
        );

        $this->assertRedirectForGetAllRoutesTasksWithoutBeingAuthenticated();

        // task_toggle
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_toggle', ['id' => 1])
        );

        $this->assertRedirectForGetAllRoutesTasksWithoutBeingAuthenticated();

        // task_delete
        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_toggle', ['id' => 1])
        );

        $this->assertRedirectForGetAllRoutesTasksWithoutBeingAuthenticated();
    }

    public function testGetTasksList(): void
    {
        $countTasks = count($this->repositoryTask->findAll());

        $this->client = $this->getClientAuthenticated();

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_list')
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $crawler = $this->client->getCrawler();
        $countTasksDom = $crawler->filter('div.row div.thumbnail')->count();

        $this->assertEquals($countTasks, $countTasksDom);
    }

    public function testCreateTask(): void
    {
        $countTasks = count($this->repositoryTask->findAll());

        $this->client = $this->getClientAuthenticated();

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_list')
        );

        $crawler = $this->client->getCrawler();
        $link = $crawler->selectLink('Créer une tâche')->link();

        $this->client->click($link);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/tasks/create', $this->client->getRequest()->getPathInfo());

        $crawler = $this->client->getCrawler();

        // FAILED
        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => '',
            'task[content]' => ''
        ]);

        $this->client->submit($form);

        $crawler = $this->client->getCrawler();

        $errors = $crawler->filter('div.invalid-feedback');

        $errorTitle = trim($errors->getNode(0)->textContent);
        $errorContent = trim($errors->getNode(1)->textContent);

        $this->assertEquals('Vous devez saisir un titre.', $errorTitle);
        $this->assertEquals('Vous devez saisir du contenu.', $errorContent);

        // SUCCESS
        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'test title',
            'task[content]' => 'test content'
        ]);

        $this->submitFormAndFollowRedirect($form);

        $this->task = $this->repositoryTask->findOneBy([
            'title' => 'test title',
            'content' => 'test content'
        ]);

        $countTasksAfterAddNewTask = count($this->repositoryTask->findAll());

        $this->assertInstanceOf(Task::class, $this->task);
        $this->assertNotNull($this->task);
        $this->assertEquals($countTasks + 1, $countTasksAfterAddNewTask);
    }

    public function testUpdateTask(): void
    {
        $this->task = $this->repositoryTask->findOneBy([
            'title' => 'test title',
            'content' => 'test content'
        ]);

        $idTask = $this->task->getId();

        $this->client = $this->getClientAuthenticated();

        // TEST NOT FOUND PAGE WITH ID NOT EXISTS

        $this->assertNotFoundPage(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_edit', ['id' => 20])
        );

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_list')
        );

        $crawler = $this->client->getCrawler();
        $link = $crawler->selectLink($this->task->getTitle())->link();

        $this->client->click($link);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/tasks/' . $idTask . '/edit', $this->client->getRequest()->getPathInfo());

        $crawler = $this->client->getCrawler();

        $form = $crawler->selectButton('Modifier')->form([
            'task[title]' => 'edit test title',
            'task[content]' => 'edit test content'
        ]);

        $this->submitFormAndFollowRedirect($form);

        $this->task = $this->repositoryTask->findOneBy(['id' => $idTask,]);

        $this->assertInstanceOf(Task::class, $this->task);
        $this->assertNotNull($this->task);
        $this->assertEquals('edit test title', $this->task->getTitle());
        $this->assertEquals('edit test content', $this->task->getContent());
    }

    public function testToggleTask(): void
    {
        $this->task = $this->repositoryTask->findOneBy([
            'title' => 'edit test title',
            'content' => 'edit test content'
        ]);

        $idTask = $this->task->getId();

        $this->client = $this->getClientAuthenticated();

        // TEST NOT FOUND PAGE WITH ID NOT EXISTS

        $this->assertNotFoundPage(
            Request::METHOD_POST,
            $this->urlGenerator->generate('task_toggle', ['id' => 20])
        );

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_list')
        );

        $crawler = $this->client->getCrawler();
        $form = $crawler->filter('form[action="/tasks/' . $idTask . '/toggle"]')->form();

        $this->submitFormAndFollowRedirect($form);

        $this->task = $this->repositoryTask->findOneBy(['id' => $idTask]);

        $this->assertInstanceOf(Task::class, $this->task);
        $this->assertNotNull($this->task);
        $this->assertTrue($this->task->isDone());
    }

    public function testDeleteTask(): void
    {
        $this->task = $this->repositoryTask->findOneBy([
            'title' => 'edit test title',
            'content' => 'edit test content'
        ]);

        $idTask = $this->task->getId();

        $this->client = $this->getClientAuthenticated();

        // TEST NOT FOUND PAGE WITH ID NOT EXISTS

        $this->assertNotFoundPage(
            Request::METHOD_POST,
            $this->urlGenerator->generate('task_delete', ['id' => 20])
        );

        $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_list')
        );

        $crawler = $this->client->getCrawler();
        $form = $crawler->filter('form[action="/tasks/' . $idTask . '/delete"]')->form();

        $this->submitFormAndFollowRedirect($form);

        $this->task = $this->repositoryTask->findOneBy(['id' => $idTask]);

        $this->assertNull($this->task);
    }

    private function assertNotFoundPage(string $method, string $path): void
    {
        $this->client->request($method, $path);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    private function assertRedirectForGetAllRoutesTasksWithoutBeingAuthenticated(): void
    {
        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        $this->client->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/login', $this->client->getRequest()->getPathInfo());
    }

    private function getClientAuthenticated(): KernelBrowser
    {
        return UtilsTests::createAuthenticatedClient(
            $this->client,
            $this->user->getUserIdentifier()
        );
    }

    private function submitFormAndFollowRedirect(Form $form): void
    {
        $this->client->submit($form);

        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        $this->client->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/tasks', $this->client->getRequest()->getPathInfo());
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $this->user = null;
        $this->repositoryTask = null;
        $this->urlGenerator = null;
    }
}
