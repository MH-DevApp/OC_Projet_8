<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var User[] $users */
        $users = $manager->getRepository(User::class)->findAll();

        /** @var array<int, array<string, string|bool>>|false $dataTasks */
        $dataTasks = json_decode(
            file_get_contents(__DIR__ . '/data/data-task.json') ?: '',
            true
        );

        if ($dataTasks) {
            foreach ($dataTasks as $dataTask) {
                /** @var string $title */
                $title = $dataTask['title'];
                /** @var string $content */
                $content = $dataTask['content'];
                /** @var bool $hasAuthor */
                $hasAuthor = $dataTask['hasAuthor'];

                $task = new Task();
                $task
                    ->setTitle($title)
                    ->setContent($content)
                ;

                if ($hasAuthor) {
                    $task->setAuthor($users[array_rand($users)]);
                }

                $manager->persist($task);
            }

            $manager->flush();
        }
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
