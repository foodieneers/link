<?php

declare(strict_types=1);

namespace Foodieneers\ApiAuth;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next, string $user): Response
    {
        $password = config("services.{$user}.inbound_password");

        if (
            $request->getUser() !== $user ||
            $request->getPassword() !== $password
        ) {
            return response('Unauthorized', 401);
        }

        return $next($request);
    }
}
