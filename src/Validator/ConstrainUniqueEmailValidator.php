<?php
declare(strict_types=1);


namespace App\Validator;

use App\Repository\UserRepository;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ConstrainUniqueEmailValidator extends ConstraintValidator
{
    public function __construct(
        private readonly UserRepository $userRepository
    )
    {

    }
    public function validate(mixed $value, Constraint $constraint):void
    {
        if(!($constraint instanceof ConstrainUniqueEmail)){
            throw new UnexpectedTypeException($constraint, ConstrainUniqueEmailValidator::class);
        }

        if(!is_string($value)){
            throw new UnexpectedValueException($value, 'string');
        }

        $user = $this->userRepository->findOneBy([
            'email'=>$value
        ]);
        if($user !== null)
            $this->context->buildViolation('not unique email')
            ->addViolation();
    }
}
