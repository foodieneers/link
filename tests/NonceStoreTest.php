<?php 

use Foodieneers\Bridge\NonceStore;

it('marks', function () {
    $nonce = new NonceStore();

    expect($nonce->mark('::key::', '::nonce::', 1,1))->toBeNull();
});

it('has seen', function () {
    $nonce = new NonceStore();

    expect($nonce->seen('::key::', '::nonce::', 1))->toBeFalse();
});