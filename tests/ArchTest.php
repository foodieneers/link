<?php

declare(strict_types=1);
use Illuminate\Support\Facades\Http;


arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

it('has the bridge macro registered', function () {
    expect(Http::hasMacro('bridge'))->toBeTrue();
});