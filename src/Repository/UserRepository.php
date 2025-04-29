<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        private TranslatorInterface $translator,
    ) {
        parent::__construct($managerRegistry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $passwordAuthenticatedUser, string $newHashedPassword): void
    {
        if (! $passwordAuthenticatedUser instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $passwordAuthenticatedUser::class));
        }

        $passwordAuthenticatedUser->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($passwordAuthenticatedUser);
        $this->getEntityManager()->flush();
    }

    public function getUserById(Uuid $uuid): User
    {
        return $this->find($uuid) ?? throw new RuntimeException($this->translator->trans('errors.user_not_found'));
    }
}
