<?php

declare(strict_types=1);

namespace Foodieneers\Bridge;

use Illuminate\Http\Request;
use Foodieneers\Bridge\BridgeConfig;
use Foodieneers\Bridge\VerificationResult;
use Foodieneers\Bridge\Exceptions\BadRequestException;

final class Verifier
{
    public function __construct(
        private readonly BridgeConfig $config = new BridgeConfig(),
        private readonly NonceStore $nonces = new NonceStore(),
    ) {}

    public function verify(Request $request): VerificationResult
    {
        $key   = (string) $request->header($this->config->headerKey());
        $tsRaw = (string) $request->header($this->config->headerTs());
        $nonce = (string) $request->header($this->config->headerNonce());
        $sig   = (string) $request->header($this->config->headerSig());

        if ($key === '' || $tsRaw === '' || $nonce === '' || $sig === '') {
            throw new BadRequestException('Missing bridge signature headers.');
        }

        if (! ctype_digit($tsRaw)) {
            throw new BadRequestException('Invalid timestamp.');
        }

        $ts = (int) $tsRaw;

        $maxAge = $this->config->maxAgeSeconds();
        $now = time();

        if ($ts <= 0 || abs($now - $ts) > $maxAge) {
            throw new BadRequestException('Request timestamp outside allowed window.');
        }

        $secret = $this->resolveSecret($key);

        $rawBody = $request->getContent() ?? '';
        $computedBodySha = $rawBody === '' ? '' : hash('sha256', $rawBody);

        $bodyHeader = (string) $request->header($this->config->headerBody());
        if ($bodyHeader !== '' && ! hash_equals($bodyHeader, $computedBodySha)) {
            throw new BadRequestException('Body hash mismatch.');
        }

        $method = strtoupper($request->getMethod());
        $pathWithQuery = $request->getRequestUri();

        $canonical = $this->canonical(
            ts: $ts,
            nonce: $nonce,
            method: $method,
            pathWithQuery: $pathWithQuery,
            bodySha256: $computedBodySha,
        );

        $expected = hash_hmac('sha256', $canonical, $secret);

        if (! hash_equals($expected, $sig)) {
            throw new BadRequestException('Invalid signature.');
        }

        if ($this->nonces->seen($key, $nonce, $ts)) {
            throw new BadRequestException('Nonce already used.');
        }

        $this->nonces->mark($key, $nonce, $ts, $this->config->nonceTtlSeconds());

        return new VerificationResult($key, $ts, $nonce);
    }

    private function canonical(
        int $ts,
        string $nonce,
        string $method,
        string $pathWithQuery,
        string $bodySha256,
    ): string {
        return implode("\n", [
            (string) $ts,
            $nonce,
            strtoupper($method),
            $pathWithQuery,
            $bodySha256,
        ]);
    }

    private function resolveSecret(string $key): string
    {
        $secret = config("services.bridge.$key.secret");

        if (is_string($secret) && $secret !== '') {
            return $secret;
        }

        throw new BadRequestException("Unknown bridge client [$key].");
    }
}
