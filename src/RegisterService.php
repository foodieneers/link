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
        Http::macro('Link', function (string $name): PendingRequest {
            
            $link = config("services.link.$name");

            throw_unless(is_array($link), InvalidArgumentException::class, "Link [$name] not configured at services.links.$name.");

            $baseUrl = mb_rtrim((string) ($link['base_url'] ?? ''), '/');

            $key = (string) $link['key'];
            $secret = (string) $link['secret'];

            throw_if($baseUrl === '', InvalidArgumentException::class, "Link [$name] missing base_url.");
            throw_if($key === '', InvalidArgumentException::class, "Link [$name] missing key.");
            throw_if($secret === '', InvalidArgumentException::class, "Link [$name] missing secret.");

            return Http::baseUrl($baseUrl)
                ->timeout(30)
                ->retry(
                    times: 5,
                    sleepMilliseconds: 200,
                    when: function (Throwable $e, $request, $response = null): bool {
                        if ($response === null) {
                            return true;
                        }
                        return $response->serverError();
                    }
                )
                ->withRequestMiddleware(function (RequestInterface $request) use ($key, $secret): RequestInterface {
                    $headers = resolve(LinkSigner::class)->headersFor($request, key: $key, secret: $secret);

                    foreach ($headers as $name => $value) {
                        $request = $request->withHeader($name, $value);
                    }

                    return $request;
                });

        });
    }
}
