<?php

declare(strict_types=1);

namespace Foodieneers\Bridge;

use Foodieneers\Bridge\Exceptions\BadRequestException;
use Illuminate\Http\Request;

final readonly class Verifier
{
    public function __construct(
        private BridgeConfig $config = new BridgeConfig(),
        private NonceStore $nonces = new NonceStore(),
    ) {}

    public function verify(Request $request): VerificationResult
    {
        $key = (string) $request->header($this->config->headerKey());
        $tsRaw = (string) $request->header($this->config->headerTs());
        $nonce = (string) $request->header($this->config->headerNonce());
        $sig = (string) $request->header($this->config->headerSig());

        throw_if($key === '' || $tsRaw === '' || $nonce === '' || $sig === '', BadRequestException::class, 'Missing bridge signature headers.');

        throw_unless(ctype_digit($tsRaw), BadRequestException::class, 'Invalid timestamp.');

        $ts = (int) $tsRaw;

        $maxAge = $this->config->maxAgeSeconds();
        $now = time();

        throw_if($ts <= 0 || abs($now - $ts) > $maxAge, BadRequestException::class, 'Request timestamp outside allowed window.');

        $secret = $this->resolveSecret($key);

        $rawBody = $request->getContent() ?? '';
        $computedBodySha = $rawBody === '' ? '' : hash('sha256', $rawBody);

        $bodyHeader = (string) $request->header($this->config->headerBody());
        throw_if($bodyHeader !== '' && ! hash_equals($bodyHeader, $computedBodySha), BadRequestException::class, 'Body hash mismatch.');

        $method = mb_strtoupper($request->getMethod());
        $pathWithQuery = $request->getRequestUri();

        $canonical = $this->canonical(
            ts: $ts,
            nonce: $nonce,
            method: $method,
            pathWithQuery: $pathWithQuery,
            bodySha256: $computedBodySha,
        );

        $expected = hash_hmac('sha256', $canonical, $secret);

        throw_unless(hash_equals($expected, $sig), BadRequestException::class, 'Invalid signature.');

        throw_if($this->nonces->seen($key, $nonce, $ts), BadRequestException::class, 'Nonce already used.');

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
            mb_strtoupper($method),
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
