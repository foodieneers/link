<?php

declare(strict_types=1);

namespace Foodieneers\Bridge;

final class BridgeConfig
{
    public function headerKey(): string
    {
        return 'X-Sign-Key';
    }

    public function headerTs(): string
    {
        return 'X-Sign-Ts';
    }

    public function headerNonce(): string
    {
        return 'X-Sign-Nonce';
    }

    public function headerBody(): string
    {
        return 'X-Sign-Sha256';
    }

    public function headerSig(): string
    {
        return 'X-Signature';
    }

    public function maxAgeSeconds(): int
    {
        return 300;
    }

    public function nonceTtlSeconds(): int
    {
        return 330;
    }
}
