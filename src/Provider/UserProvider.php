<?php

namespace Esites\EncryptionBundle\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Esites\EncryptionBundle\Exception\InvalidUserClassException;
use Esites\EncryptionBundle\Exception\InvalidUsernameFieldException;
use Esites\EncryptionBundle\Helper\EncryptionHelper;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var EncryptionHelper
     */
    private $encryptionHelper;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var string
     */
    private $userClass;

    /**
     * @var string
     */
    private $usernameField;


    public function __construct(
        EncryptionHelper $encryptionHelper,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        ?string $userClass,
        ?string $usernameField
    ) {
        $this->encryptionHelper = $encryptionHelper;
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->userClass = $userClass;
        $this->usernameField = $usernameField;
    }

    /**
     * @throws InvalidUsernameFieldException
     */
    public function loadUserByUsername($username): UserInterface
    {
        $hashedUsername = $this->encryptionHelper->hash($username);

        if ($this->usernameField === null) {
            throw new InvalidUsernameFieldException('Define the username field in config');
        }

        $user = $this->entityManager->getRepository($this->userClass)->findOneBy([
            $this->usernameField => $hashedUsername,
        ]);

        if (!$user instanceof UserInterface) {
            throw new UsernameNotFoundException();
        }

        return $user;
    }

    /**
     * @throws InvalidUserClassException
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if ($this->userClass === null) {
            throw new InvalidUserClassException('Define the user class in config');
        }

        /** @var EntityRepository $userRepository */
        $userRepository = $this->entityManager->getRepository($this->userClass);
        $id = $this->entityManager->getUnitOfWork()->getEntityIdentifier($user);

        /** @var UserInterface|null $user */
        $user = $userRepository->find($id);

        return clone $user;
    }

    public function supportsClass($class): bool
    {
        return $class === $this->userClass;
    }
}
