<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\User;
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
    public function testUserInstance($username, $email, $password)
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($password);

        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($password, $user->getPassword());
        $this->assertNull($user->getId());
    }

    public function dataProviderSomeUsers()
    {
        return [
            [
                'username' => 'user',
                'email' => 'user@test.fr',
                'password' => '123456'
            ],
            [
                'username' => 'user1',
                'email' => 'user1@test.fr',
                'password' => '123456'
            ],
            [
                'username' => 'user2',
                'email' => 'user2@test.fr',
                'password' => '123456'
            ],
            [
                'username' => 'user3',
                'email' => 'user3@test.fr',
                'password' => '123456'
            ],
            [
                'username' => 'user4',
                'email' => 'user4@test.fr',
                'password' => '123456'
            ]
        ];
    }
}
