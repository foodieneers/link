<?php

declare(strict_types=1);

namespace Foodieneers\Bridge;

final readonly class VerificationResult
{
    public function __construct(
        public string $key,
        public int $ts,
        public string $nonce,
    ) {}
}
