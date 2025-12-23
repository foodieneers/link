<?php

declare(strict_types=1);

namespace Foodieneers\Link;

final readonly class VerificationResult
{
    public function __construct(
        public string $key,
        public int $ts,
        public string $nonce,
    ) {}
}
