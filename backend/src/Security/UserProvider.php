<?php


namespace App\Security;


use App\Model\User;
use App\Service\User\UserRepository;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername(string $username): User
    {
        if (!$user = $this->userRepository->getUserByEmail($username)) {
            throw new UsernameNotFoundException();
        }

        $user->setUsername($username);

        return $user;
    }

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param User $user
     * @return User
     */
    public function refreshUser(UserInterface $user): User
    {
        if (!$refreshedUser = $this->userRepository->getUserByEmail($user->getEmail())) {
            throw new UsernameNotFoundException();
        }

        $refreshedUser->setUsername($user->getUsername());

        return $user;
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @return bool
     */
    public function supportsClass(string $class)
    {
        return $class === User::class;
    }
}
