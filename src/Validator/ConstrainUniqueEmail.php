<?php
declare(strict_types=1);


namespace App\Validator;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ConstrainUniqueEmail extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }

    public function validatedBy():string
    {
        return self::class.'Validator';
    }

    public function __sleep(): array
    {
        return array_merge(
            [
                'mode'
            ]
        );
    }
}


