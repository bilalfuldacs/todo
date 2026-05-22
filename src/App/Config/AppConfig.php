<?php

declare(strict_types=1);

namespace App\Config;

final class AppConfig
{
    private static ?self $instance = null;

    private function __construct(
        public readonly string $name,
        public readonly string $env,
        public readonly bool $debug,
        public readonly string $url,
        public readonly DatabaseConfig $database,
        public readonly string $corsOrigins,
        public readonly int $tokenTtlDays,
    ) {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self(
                name: (string) ($_ENV['APP_NAME'] ?? 'Todo API'),
                env: (string) ($_ENV['APP_ENV'] ?? 'production'),
                debug: filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
                url: rtrim((string) ($_ENV['APP_URL'] ?? ''), '/'),
                database: new DatabaseConfig(
                    host: (string) ($_ENV['DB_HOST'] ?? '127.0.0.1'),
                    port: (int) ($_ENV['DB_PORT'] ?? 3306),
                    database: (string) ($_ENV['DB_DATABASE'] ?? 'todo'),
                    username: (string) ($_ENV['DB_USERNAME'] ?? 'root'),
                    password: (string) ($_ENV['DB_PASSWORD'] ?? ''),
                    charset: 'utf8mb4',
                ),
                corsOrigins: (string) ($_ENV['CORS_ALLOWED_ORIGINS'] ?? '*'),
                tokenTtlDays: max(1, (int) ($_ENV['TOKEN_TTL_DAYS'] ?? 7)),
            );
        }

        return self::$instance;
    }

    public function isProduction(): bool
    {
        return $this->env === 'production';
    }

    /** @internal For tests only */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }
}
