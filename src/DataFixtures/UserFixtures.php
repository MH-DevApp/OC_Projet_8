<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $dataUsers = json_decode(file_get_contents(__DIR__ . '/data/data-user.json'), true);

        foreach ($dataUsers as $dataUser) {
            $user = new User();
            $user
                ->setUsername($dataUser['username'])
                ->setEmail($dataUser['email'])
                ->setPassword(
                    $this->passwordHasher->hashPassword(
                        $user,
                        '123456'
                    )
                )
                ->setRoles($dataUser['roles'])
            ;

            $manager->persist($user);
        }

        $manager->flush();
    }
}
