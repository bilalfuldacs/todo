<?php

declare(strict_types=1);

namespace App\Http;

final class Response
{
    public function __construct(
        private readonly array $body,
        private readonly int $status = 200,
    ) {
    }

    public static function json(array $body, int $status = 200): self
    {
        return new self($body, $status);
    }

    public static function success(mixed $data = null, ?string $message = null, int $status = 200): self
    {
        $body = ['success' => true];
        if ($message !== null) {
            $body['message'] = $message;
        }
        if ($data !== null) {
            $body['data'] = $data;
        }

        return new self($body, $status);
    }

    public function send(): void
    {
        http_response_code($this->status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /** @return array<string, mixed> */
    public function getBody(): array
    {
        return $this->body;
    }
}
