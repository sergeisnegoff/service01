<?php


namespace App\Security\Voter;


use App\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends AbstractVoter
{
    protected function getAttributeVotes(): array
    {
        return [
            'supplier' => [$this, 'voteSupplier'],
            'buyer' => [$this, 'voteBuyer'],
        ];
    }

    protected function supportsSubject($subject): bool
    {
        return $subject instanceof User;
    }

    protected function voteSupplier(User $user, TokenInterface $token)
    {
        $company = $user->getCompanyRelatedByActiveCompanyId();

        if (!$company) {
            return false;
        }

        return $company->isSupplierCompany();
    }

    protected function voteBuyer(User $user, TokenInterface $token)
    {
        $company = $user->getCompanyRelatedByActiveCompanyId();

        if (!$company) {
            return false;
        }

        return $company->isBuyerCompany();
    }
}
