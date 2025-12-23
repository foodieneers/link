<?php

declare(strict_types=1);

use Foodieneers\Link\LinkConfig;

it('has correct headers', function (): void {
    $config = new LinkConfig();

    expect($config->headerKey())->toBe('X-Sign-Key');
    expect($config->headerTs())->toBe('X-Sign-Ts');
    expect($config->headerNonce())->toBe('X-Sign-Nonce');
    expect($config->headerBody())->toBe('X-Sign-Sha256');
    expect($config->headerSig())->toBe('X-Signature');
});

it('has correct data', function () {
    $config = new LinkConfig();

    expect($config->maxAgeSeconds())->toBe(300);
    expect($config->nonceTtlSeconds())->toBe(330);
});
