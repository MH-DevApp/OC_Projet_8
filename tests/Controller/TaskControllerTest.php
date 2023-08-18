<?php

namespace App\Tests\Controller;

use App\Repository\TaskRepository;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use http\Client;
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

        /** @var EntityManagerInterface $manager */
        $manager = $this->client
            ->getContainer()
            ->get('doctrine');

        $this->user = $manager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'admin']);

        /** @var TaskRepository $taskRepository */
        $taskRepository = $manager->getRepository(Task::class);

        $this->repositoryTask = $taskRepository;

        /** @var ?UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $this->client
            ->getContainer()
            ->get('router');

        $this->urlGenerator = $urlGenerator;
    }

    public function testAllRoutesTasksWithoutBeingAuthenticated(): void
    {
        /** @var KernelBrowser $client */
        $client = $this->client;

        // task_list
        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('task_list') ?? ''
        );

        $this->assertRedirectForGetAllRoutesTasksWithoutBeingAuthenticated();

        // task_create
        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('task_create') ?? ''
        );

        $this->assertRedirectForGetAllRoutesTasksWithoutBeingAuthenticated();

        // task_edit
        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('task_edit', ['id' => 1]) ?? ''
        );

        $this->assertRedirectForGetAllRoutesTasksWithoutBeingAuthenticated();

        // task_toggle
        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('task_toggle', ['id' => 1]) ?? ''
        );

        $this->assertRedirectForGetAllRoutesTasksWithoutBeingAuthenticated();

        // task_delete
        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('task_delete', ['id' => 1]) ?? ''
        );

        $this->assertRedirectForGetAllRoutesTasksWithoutBeingAuthenticated();
    }

    public function testGetTasksList(): void
    {
        $countTasks = count($this->repositoryTask?->findAll() ?? []);

        $client = $this->getClientAuthenticated();

        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('task_list') ?? ''
        );

        $this->assertEquals(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode()
        );

        $crawler = $client->getCrawler();
        $countTasksDom = $crawler->filter('div.row div.thumbnail')->count();

        $this->assertEquals($countTasks, $countTasksDom);
    }

    public function testCreateTask(): void
    {
        $countTasks = count($this->repositoryTask?->findAll() ?? []);

        $client = $this->getClientAuthenticated();

        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('task_list') ?? ''
        );

        $crawler = $client->getCrawler();
        $link = $crawler->selectLink('Créer une tâche')->link();

        $client->click($link);

        $this->assertEquals(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode()
        );
        $this->assertEquals(
            '/tasks/create',
            $client->getRequest()->getPathInfo()
        );

        $crawler = $client->getCrawler();

        // FAILED
        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => '',
            'task[content]' => ''
        ]);

        $client->submit($form);

        $crawler = $client->getCrawler();

        $errors = $crawler->filter('div.invalid-feedback');

        $errorTitle = trim($errors->getNode(0)?->textContent ?? '');
        $errorContent = trim($errors->getNode(1)?->textContent ?? '');

        $this->assertEquals(
            'Vous devez saisir un titre.',
            $errorTitle
        );
        $this->assertEquals(
            'Vous devez saisir du contenu.',
            $errorContent
        );

        // SUCCESS
        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'test title',
            'task[content]' => 'test content'
        ]);

        $this->submitFormAndFollowRedirect($form);

        $this->task = $this->repositoryTask?->findOneBy([
            'title' => 'test title',
            'content' => 'test content'
        ]);

        $countTasksAfterAddNewTask = count($this->repositoryTask?->findAll() ?? []);

        $this->assertInstanceOf(Task::class, $this->task);
        $this->assertNotNull($this->task);
        $this->assertEquals($countTasks + 1, $countTasksAfterAddNewTask);

        // Anomaly correction => User linked during task creation (new feature)
        $this->assertEquals(
            $this->task?->getAuthor()?->getUserIdentifier() ?? 'author of task',
            $this->user?->getUserIdentifier() ?? 'authenticated user'
        );
    }

    public function testUpdateTask(): void
    {
        $this->task = $this->repositoryTask?->findOneBy([
            'title' => 'test title',
            'content' => 'test content'
        ]);

        $idTask = $this->task?->getId() ?? -1;

        $this->client = $this->getClientAuthenticated();

        $client = $this->client;

        // TEST NOT FOUND PAGE WITH ID NOT EXISTS

        $this->assertNotFoundPage(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('task_edit', ['id' => 20]) ?? ''
        );

        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('task_list') ?? ''
        );

        $crawler = $client->getCrawler();
        $link = $crawler->selectLink($this->task?->getTitle() ?? '')->link();

        $client->click($link);

        $this->assertEquals(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode()
        );
        $this->assertEquals(
            '/tasks/' . $idTask . '/edit',
            $client->getRequest()->getPathInfo()
        );

        $crawler = $client->getCrawler();

        $form = $crawler->selectButton('Modifier')->form([
            'task[title]' => 'edit test title',
            'task[content]' => 'edit test content'
        ]);

        $this->submitFormAndFollowRedirect($form);

        $task = $this->repositoryTask?->findOneBy(['id' => $idTask]);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertNotNull($task);
        $this->assertEquals(
            'edit test title',
            $task->getTitle()
        );
        $this->assertEquals(
            'edit test content',
            $task->getContent()
        );
    }

    public function testToggleTask(): void
    {
        $this->task = $this->repositoryTask?->findOneBy([
            'title' => 'edit test title',
            'content' => 'edit test content'
        ]);

        $idTask = $this->task?->getId() ?? -1;

        $this->client = $this->getClientAuthenticated();
        $client = $this->client;

        // TEST NOT FOUND PAGE WITH ID NOT EXISTS

        $this->assertNotFoundPage(
            Request::METHOD_POST,
            $this->urlGenerator?->generate(
                'task_toggle',
                ['id' => 20]
            ) ?? ''
        );

        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('task_list') ?? ''
        );

        $crawler = $client->getCrawler();
        $form = $crawler->filter('form[action="/tasks/' . $idTask . '/toggle"]')->form();

        $this->submitFormAndFollowRedirect($form);

        $task = $this->repositoryTask?->findOneBy(['id' => $idTask]);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertNotNull($task);
        $this->assertTrue($task->isDone());
    }

    public function testDeleteTaskByOtherAuthor(): void
    {
        $this->task = $this->repositoryTask?->findOneBy([
            'title' => 'edit test title',
            'content' => 'edit test content'
        ]);

        $idTask = $this->task?->getId() ?? -1;

        /** @var KernelBrowser $client */
        $client = $this->client;
        $client = UtilsTests::createAuthenticatedClient($client, 'user');

        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('task_list') ?? ''
        );

        // Test if the delete button is hide
        $crawler = $client->getCrawler();
        $form = $crawler->filter('form[action="/tasks/' . $idTask . '/delete"]')->getNode(0);

        $this->assertNull($form);

        // test delete task with direct url by Id
        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('task_delete', ['id' => $idTask]) ?? ''
        );

        $this->assertEquals(Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode());

        $task = $this->repositoryTask?->findOneBy(['id' => $idTask]);

        $this->assertNotNull($task);
    }

    public function testDeleteTaskByItsOwnAuthor(): void
    {
        $this->task = $this->repositoryTask?->findOneBy([
            'title' => 'edit test title',
            'content' => 'edit test content'
        ]);

        $idTask = $this->task?->getId() ?? -1;

        $this->client = $this->getClientAuthenticated();
        $client = $this->client;

        // TEST NOT FOUND PAGE WITH ID NOT EXISTS

        $this->assertNotFoundPage(
            Request::METHOD_POST,
            $this->urlGenerator?->generate(
                'task_delete',
                ['id' => 20]
            ) ?? ''
        );

        $client->request(
            Request::METHOD_GET,
            $this->urlGenerator?->generate('task_list') ?? ''
        );

        $crawler = $client->getCrawler();
        $form = $crawler->filter('form[action="/tasks/' . $idTask . '/delete"]')->form();

        $this->submitFormAndFollowRedirect($form);

        $task = $this->repositoryTask?->findOneBy(['id' => $idTask]);

        $this->assertNull($task);
    }

    private function assertNotFoundPage(string $method, string $path): void
    {
        $this->client?->request($method, $path);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client?->getResponse()->getStatusCode());
    }

    private function assertRedirectForGetAllRoutesTasksWithoutBeingAuthenticated(): void
    {
        $this->assertEquals(Response::HTTP_FOUND, $this->client?->getResponse()->getStatusCode());

        $this->client?->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->client?->getResponse()->getStatusCode());
        $this->assertEquals('/login', $this->client?->getRequest()->getPathInfo());
    }

    private function getClientAuthenticated(): KernelBrowser
    {
        /** @var KernelBrowser $client */
        $client = $this->client;
        return UtilsTests::createAuthenticatedClient(
            $client,
            $this->user?->getUserIdentifier() ?? ''
        );
    }

    private function submitFormAndFollowRedirect(Form $form): void
    {
        $this->client?->submit($form);

        $this->assertEquals(Response::HTTP_FOUND, $this->client?->getResponse()->getStatusCode());

        $this->client?->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->client?->getResponse()->getStatusCode());
        $this->assertEquals('/tasks', $this->client?->getRequest()->getPathInfo());
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
