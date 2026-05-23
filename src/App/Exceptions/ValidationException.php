<?php

declare(strict_types=1);

namespace App\Exceptions;

final class ValidationException extends HttpException
{
    public function __construct(string $message = 'Validation failed', array $errors = [])
    {
        parent::__construct($message, 422, $errors);
    }
}
