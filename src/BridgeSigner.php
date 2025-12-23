<?php

declare(strict_types=1);

namespace Foodieneers\Bridge;

use Illuminate\Http\Client\Request;
use Psr\Http\Message\RequestInterface;

final readonly class BridgeSigner
{
    public function __construct(
        private BridgeConfig $config = new BridgeConfig(),
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

/**
 * Helper to extract path and query from PSR-7 UriInterface
 */
private function pathWithQueryFromUri(\Psr\Http\Message\UriInterface $uri): string
{
    $path = $uri->getPath() ?: '/';
    $query = $uri->getQuery();

    return ($query !== '') ? "{$path}?{$query}" : $path;
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

    private function nonce(): string
    {
        return bin2hex(random_bytes(16));
    }

    private function pathWithQuery(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        $query = parse_url($url, PHP_URL_QUERY);

        return (is_string($query) && $query !== '')
            ? "{$path}?{$query}"
            : $path;
    }
}
