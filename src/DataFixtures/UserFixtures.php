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
        /** @var array<int, array<string, string|array<int, string>>>|false $dataUsers */
        $dataUsers = json_decode(
            file_get_contents(__DIR__ . '/data/data-user.json') ?: '',
            true
        );

        if ($dataUsers) {
            foreach ($dataUsers as $dataUser) {
                /** @var string $username */
                $username = $dataUser['username'];
                /** @var string $email */
                $email = $dataUser['email'];
                /** @var array<int, string> $roles */
                $roles = $dataUser['roles'];

                $user = new User();
                $user
                    ->setUsername($username)
                    ->setEmail($email)
                    ->setPassword(
                        $this->passwordHasher->hashPassword(
                            $user,
                            '123456'
                        )
                    )
                    ->setRoles($roles)
                ;

                $manager->persist($user);
            }

            $manager->flush();
        }
    }
}
