<?php

declare(strict_types=1);

namespace Foodieneers\Bridge;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Throwable;

final class RegisterService
{
    public function __invoke(): void
    {
        Http::macro('bridge', function (string $name): PendingRequest {
            $bridge = config("services.bridge.$name");

            throw_unless(is_array($bridge), InvalidArgumentException::class, "Bridge [$name] not configured at services.bridges.$name.");

            $baseUrl = mb_rtrim((string) ($bridge['base_url'] ?? ''), '/');
            $timeout = 10;

            $key = (string) ($bridge['key'] ?? '');
            $secret = (string) ($bridge['secret'] ?? '');

            throw_if($baseUrl === '' || $key === '' || $secret === '', InvalidArgumentException::class, "Bridge [$name] missing base_url and/or signing.key/signing.secret.");

            return Http::baseUrl($baseUrl)
                ->timeout($timeout)
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
                    $headers = resolve(BridgeSigner::class)->headersFor($request, key: $key, secret: $secret);

                    foreach ($headers as $name => $value) {
                        $request = $request->withHeader($name, $value);
                    }

                    return $request;
                });

        });
    }
}
