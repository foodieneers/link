<?php

declare(strict_types=1);

namespace Foodieneers\Bridge\Http\Middleware;

use Closure;
use Foodieneers\Bridge\Exceptions\BadRequestException;
use Foodieneers\Bridge\Verifier;
use Illuminate\Http\Request;

final readonly class VerifyBridgeRequest
{
    public function __construct(
        private Verifier $verifier,
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        try {
            $result = $this->verifier->verify($request);

            $request->attributes->set('bridge.key', $result->key);

            return $next($request);
        } catch (BadRequestException) {
            return response()->json([
                'message' => 'Bad Request',
            ], 400);
        }
    }
}
