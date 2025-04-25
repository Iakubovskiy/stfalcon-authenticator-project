<?php

declare(strict_types=1);

namespace App\Tests;

use App\ValueObjects\Email;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Email::class)]
final class EmailValidationTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public function testNotValidEmailValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches(' /is not a valid email address/');

        Email::fromString('fggfgfv');
    }
}
