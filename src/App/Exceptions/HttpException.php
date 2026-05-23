<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class HttpException extends Exception
{
    public function __construct(
        string $message,
        private readonly int $statusCode = 400,
        private readonly ?array $errors = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }
}
