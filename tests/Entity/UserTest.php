<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * @dataProvider dataProviderSomeUsers
     *
     * @param string $username
     * @param string $email
     * @param string $password
     *
     * @return void
     */
    public function testUserInstance(
        string $username,
        string $email,
        string $password,
        array $roles
    ): void
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setRoles($roles);

        $this->assertEquals($username, $user->getUserIdentifier());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($password, $user->getPassword());
        $this->assertEquals([...$roles, "ROLE_USER"], $user->getRoles());
        $this->assertNull($user->getId());
    }

    public function dataProviderSomeUsers(): array
    {
        return [
            [
                'username' => 'user',
                'email' => 'user@test.fr',
                'password' => '123456',
                'roles' => []
            ],
            [
                'username' => 'user1',
                'email' => 'user1@test.fr',
                'password' => '123456',
                'roles' => ['ROLE_ADMIN']
            ],
            [
                'username' => 'user2',
                'email' => 'user2@test.fr',
                'password' => '123456',
                'roles' => []
            ],
            [
                'username' => 'user3',
                'email' => 'user3@test.fr',
                'password' => '123456',
                'roles' => []
            ],
            [
                'username' => 'user4',
                'email' => 'user4@test.fr',
                'password' => '123456',
                'roles' => ['ROLE_ADMIN']
            ]
        ];
    }
}
