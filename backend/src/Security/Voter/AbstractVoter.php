<?php


namespace App\Security\Voter;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractVoter extends Voter
{
    abstract protected function getAttributeVotes(): array;

    abstract protected function supportsSubject($subject): bool;

    protected function supports($attribute, $subject)
    {
        $votes = $this->getAttributeVotes();

        if (!is_callable($votes[$attribute] ?? null)) {
            return false;
        }

        return $this->supportsSubject($subject);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $votes = $this->getAttributeVotes();
        $vote = $votes[$attribute] ?? null;

        if (!is_callable($vote)) {
            return true;
        }

        return $vote($subject, $token);
    }
}
