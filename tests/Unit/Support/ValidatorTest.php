<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Exceptions\ValidationException;
use App\Support\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function test_required_passes_with_all_fields(): void
    {
        Validator::required(['email' => 'a@b.com', 'password' => 'x'], ['email', 'password']);
        $this->addToAssertionCount(1);
    }

    public function test_required_throws_for_missing_field(): void
    {
        $this->expectException(ValidationException::class);
        Validator::required(['email' => 'a@b.com'], ['email', 'password']);
    }

    public function test_email_rejects_invalid(): void
    {
        $this->expectException(ValidationException::class);
        Validator::email('not-an-email');
    }
}
