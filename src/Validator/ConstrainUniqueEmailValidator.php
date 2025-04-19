<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConstrainUniqueEmailValidator extends ConstraintValidator
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator,
    ) {

    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! ($constraint instanceof ConstrainUniqueEmail)) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        if ($value === null) {
            return;
        }

        if (! is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $tokenUserId = $this->tokenStorage->getToken()?->getUserIdentifier();
        if($tokenUserId !== null) {
            $tokenUser = $this->userRepository->find($tokenUserId);
            if ($tokenUser !== null && $tokenUser->getEmail() === $value) {
                return;
            }
        }

        $user = $this->userRepository->findOneBy([
            'email' => $value,
        ]);
        if ($user !== null) {
            $this->context->buildViolation($this->translator->trans('errors.not_unique_email'))
                ->addViolation();
        }
    }
}
