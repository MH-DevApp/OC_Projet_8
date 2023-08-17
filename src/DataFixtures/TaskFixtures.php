<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $dataTasks = json_decode(file_get_contents(__DIR__ . '/data/data-task.json'), true);

        foreach ($dataTasks as $dataTask) {
            $task = new Task();
            $task
                ->setTitle($dataTask['title'])
                ->setContent($dataTask['content']);
            ;

            $manager->persist($task);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
