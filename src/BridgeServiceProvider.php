<?php

declare(strict_types=1);

namespace Foodieneers\Bridge;

use Foodieneers\Bridge\Http\Middleware\VerifyBridgeRequest;
use Illuminate\Routing\Router;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class BridgeServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('bridge');
    }

    public function bootingPackage(): void
    {
        resolve(RegisterService::class)();
    }

    public function packageBooted(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('verify.bridge', VerifyBridgeRequest::class);
    }
}
