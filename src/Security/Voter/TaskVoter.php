<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TaskVoter extends Voter
{
    public const DELETE = 'TASK_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::DELETE
            && $subject instanceof Task;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        /** @var Task $task */
        $task = $subject;

        if ($task->getAuthor() === $user) {
            return true;
        }

        return false;
    }
}
