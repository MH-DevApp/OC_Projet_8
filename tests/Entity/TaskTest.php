<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use DateTime;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    /**
     * @dataProvider dataProviderSomeTasks
     *
     * @param string $title
     * @param string $content
     * @param bool $isDone
     *
     * @return void
     */
    public function testTaskInstance(
        string $title,
        string $content,
        bool $isDone
    ): void
    {
        $task = new Task();
        $task->setTitle($title);
        $task->setContent($content);
        $task->toggle($isDone);

        $this->assertEquals($title, $task->getTitle());
        $this->assertEquals($content, $task->getContent());
        $this->assertEquals($isDone, $task->isDone());
        $this->assertInstanceOf(DateTime::class, $task->getCreatedAt());
        $this->assertNull($task->getId());

        $createdAt = new DateTime('now');
        $task->setCreatedAt($createdAt);
        $this->assertEquals($createdAt, $task->getCreatedAt());
    }

    public function dataProviderSomeTasks(): array
    {
        return [
            [
                'title' => 'Task',
                'content' => 'Task',
                'isDone' => true
            ],
            [
                'title' => 'Task1',
                'content' => 'Task1',
                'isDone' => false
            ],
            [
                'title' => 'Task2',
                'content' => 'Task2',
                'isDone' => true
            ],
            [
                'title' => 'Task3',
                'content' => 'Task3',
                'isDone' => true
            ],
            [
                'title' => 'Task4',
                'content' => 'Task4',
                'isDone' => false
            ]
        ];
    }
}
