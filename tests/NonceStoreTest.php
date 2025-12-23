<?php

declare(strict_types=1);

use Foodieneers\Link\NonceStore;

it('marks', function () {
    $nonce = new NonceStore();

    expect($nonce->mark())->toBeNull();
});

it('has seen', function () {
    $nonce = new NonceStore();

    expect($nonce->seen())->toBeFalse();
});
