<?php

declare(strict_types=1);

namespace Foodieneers\Link;

use Foodieneers\Link\Http\Middleware\VerifyLinkRequest;
use Illuminate\Routing\Router;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class LinkServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('Link');
    }

    public function bootingPackage(): void
    {
        resolve(RegisterService::class)();
    }

    public function packageBooted(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('verify.Link', VerifyLinkRequest::class);
    }
}
