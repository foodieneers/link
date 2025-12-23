<?php

declare(strict_types=1);

namespace Foodieneers\Bridge;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Http;
use Foodieneers\Bridge\RegisterService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Foodieneers\Bridge\Http\Middleware\VerifyBridgeRequest;

final class BridgeServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('bridge');
    }

    public function bootingPackage(): void 
    {
        app(RegisterService::class)();
    }
    public function packageBooted(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('verify.bridge', VerifyBridgeRequest::class);
    }
}
