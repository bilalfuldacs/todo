<?php

declare(strict_types=1);

namespace App\Support;

final class TokenHasher
{
    public static function hash(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }
}
