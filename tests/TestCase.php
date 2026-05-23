<?php

declare(strict_types=1);

namespace Tests;

use App\Config\AppConfig;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        AppConfig::resetInstance();
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['APP_DEBUG'] = 'true';
        $_ENV['TOKEN_TTL_DAYS'] = '7';
        AppConfig::getInstance();
    }

    protected function tearDown(): void
    {
        AppConfig::resetInstance();
        parent::tearDown();
    }

    /** @param array<string, mixed> $body */
    protected function assertResponseSuccess(
        \App\Http\Response $response,
        int $expectedStatus = 200,
        ?string $messageContains = null,
    ): array {
        $this->assertSame($expectedStatus, $response->getStatus());
        $body = $response->getBody();
        $this->assertTrue($body['success'] ?? false);

        if ($messageContains !== null) {
            $this->assertStringContainsString($messageContains, (string) ($body['message'] ?? ''));
        }

        return $body;
    }
}
