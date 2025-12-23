<?php

declare(strict_types=1);

namespace Foodieneers\Link;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Throwable;

final class RegisterService
{
    public function __invoke(): void
    {
        Http::macro('link', function (string $key): PendingRequest {
            
            $link = config("services.link.$key");

            throw_unless(is_array($link), InvalidArgumentException::class, "Link [$key] not configured at services.links.$key.");

            $baseUrl = mb_rtrim((string) ($link['base_url'] ?? ''), '/');

            $secret = (string) $link['secret'];

            throw_if($baseUrl === '', InvalidArgumentException::class, "Link [$key] missing base_url.");
            throw_if($secret === '', InvalidArgumentException::class, "Link [$key] missing secret.");

            return Http::baseUrl($baseUrl)
                ->timeout(30)
                ->retry(
                    times: 5,
                    sleepMilliseconds: 200,
                    when: function (Throwable $e, $request, $response = null): bool {
                        dd($e);
                        if ($response === null) {
                            return true;
                        }
                        return $response->serverError();
                    }
                )
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
