<?php

declare(strict_types=1);

namespace Foodieneers\Link;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

final readonly class Signer
{
    public function __construct(
        private LinkConfig $config = new LinkConfig(),
    ) {}

    /**
     * @param  RequestInterface  $request  Laravel HTTP client middleware request object
     * @return array<string, string>
     */
    public function headersFor(RequestInterface $request, string $key, string $secret): array
    {
        $ts = time();
        $nonce = $this->nonce();

        $method = mb_strtoupper($request->getMethod());

        $uri = $request->getUri();
        $pathWithQuery = $this->pathWithQueryFromUri($uri);

        $body = (string) $request->getBody();
        $bodySha = $body === '' ? '' : hash('sha256', $body);

        $canonical = $this->canonical(
            ts: $ts,
            nonce: $nonce,
            method: $method,
            pathWithQuery: $pathWithQuery,
            bodySha256: $bodySha,
        );

        $signature = hash_hmac('sha256', $canonical, $secret);

        $headers = [
            $this->config->headerKey() => $key,
            $this->config->headerTs() => (string) $ts,
            $this->config->headerNonce() => $nonce,
            $this->config->headerSig() => $signature,
        ];

        if ($bodySha !== '') {
            $headers[$this->config->headerBody()] = $bodySha;
        }

        return $headers;
    }

    public function canonical(
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

    /**
     * Helper to extract path and query from PSR-7 UriInterface
     */
    private function pathWithQueryFromUri(UriInterface $uri): string
    {
        $path = $uri->getPath() ?: '/';
        $query = $uri->getQuery();

        return ($query !== '') ? "{$path}?{$query}" : $path;
    }

    private function nonce(): string
    {
        return bin2hex(random_bytes(16));
    }
}
