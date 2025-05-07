<?php

declare(strict_types=1);

namespace App\User\Email\Validator;

use App\User\Support\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueEmailValidator extends ConstraintValidator
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly TokenStorageInterface $tokenStorage,
    ) {

    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! ($constraint instanceof UniqueEmail)) {
            throw new UnexpectedTypeException($constraint, __CLASS__);
        }

        if (! is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $currentUserToken = $this->tokenStorage->getToken();
        if ($currentUserToken instanceof TokenInterface) {
            $currentUser = $this->userRepository->getUserById(Uuid::fromString($currentUserToken->getUserIdentifier()));
            if ($currentUser->getEmail() === $value) {
                return;
            }
        }

        $user = $this->userRepository->findOneBy([
            'email.email' => $value,
        ]);
        if ($user !== null) {
            $this->context->buildViolation('not unique email')
                ->addViolation();
        }
    }
}
