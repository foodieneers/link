<?php

declare(strict_types=1);

namespace Foodieneers\ApiAuth;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class ApiAuthServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {

        $package
            ->name('api-auth');
    }

    public function packageBooted(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('api.auth', ApiAuthMiddleware::class);

        Http::macro('user', function (string $user) {
            $password = config("services.{$user}.outbound_password");
            $baseUrl = mb_rtrim((string) config("services.{$user}.endpoint"), '/');

            return Http::withBasicAuth($user, $password)
                ->retry(5, 100)
                ->baseUrl($baseUrl);
        });
    }
}
