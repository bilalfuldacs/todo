<?php

declare(strict_types=1);

namespace App\Support;

use App\Exceptions\ValidationException;

final class Validator
{
    /** @param array<string, mixed> $data */
    public static function required(array $data, array $fields): void
    {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                $errors[$field] = self::label($field) . ' is required';
            }
        }

        if ($errors !== []) {
            throw new ValidationException(errors: $errors);
        }
    }

    public static function email(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException(errors: ['email' => 'Invalid email address']);
        }
    }

    public static function password(string $password, int $minLength = 8): void
    {
        if (strlen($password) < $minLength) {
            throw new ValidationException(
                errors: ['password' => "Password must be at least {$minLength} characters"]
            );
        }
    }

    private static function label(string $field): string
    {
        return ucfirst(str_replace('_', ' ', $field));
    }
}
