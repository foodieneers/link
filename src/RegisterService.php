<?php

declare(strict_types=1);

namespace Foodieneers\Link;

use Throwable;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\RequestInterface;
use Illuminate\Http\Client\PendingRequest;

final class RegisterService
{
    public function __invoke(): void
    {
        Http::macro('link', function (string $key): PendingRequest {
            
            $link = config("services.link.$key");

            throw_unless(is_array($link), InvalidArgumentException::class, "Link [$key] not configured at services.links.$key.");

            $baseUrl = $link['base_url'] ?? '';
            $secret = $link['secret'] ?? '';

            if (!is_string($baseUrl) || !is_string($secret)) {
                throw new InvalidArgumentException("Config for [$key] must contain strings for base_url and secret.");
            }

            return Http::baseUrl(mb_rtrim($baseUrl, '/'))
                ->timeout(30)
                ->retry(5, 200, 
                    when: function (Throwable $e, Request $r): bool {
                        $response = $r->response;
                        return ! $response instanceof Response || $response->serverError();
                    })
                ->withRequestMiddleware(function (RequestInterface $request) use ($key, $secret): RequestInterface {
                    $headers = resolve(Signer::class)->headersFor($request, key: $key, secret: $secret);
                    foreach ($headers as $name => $value) {
                        $request = $request->withHeader($name, $value);
                    }
                    return $request;
                });

        });
    }
}
