<?php

declare(strict_types=1);

namespace Foodieneers\Link\Http\Middleware;

use Closure;
use Foodieneers\Link\Exceptions\BadRequestException;
use Foodieneers\Link\Verifier;
use Illuminate\Http\Request;

final readonly class VerifyLinkRequest
{
    public function __construct(
        private Verifier $verifier,
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        try {
            $result = $this->verifier->verify($request);

            $request->attributes->set('link.key', $result->key);

            return $next($request);
        } catch (BadRequestException) {
            return response()->json([
                'message' => 'Bad Request',
            ], 400);
        }
    }
}
