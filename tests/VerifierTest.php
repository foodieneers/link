<?php

declare(strict_types=1);

use Foodieneers\Link\Verifier;
use Illuminate\Http\Request;

it('verifies a request in total isolation', function () {
    // 1. Setup predictable values
    $fixedTime = now()->timestamp;
    $key = 'test-app';
    $secret = 'pure-unit-secret';
    $nonce = 'random-nonce-123';
    $method = 'POST';
    $uri = '/test-path';
    $body = '{"foo":"bar"}';
    $bodySha = hash('sha256', $body);

    // 2. Build the canonical string exactly as the class does
    $canonical = implode("\n", [
        (string) $fixedTime,
        $nonce,
        $method,
        $uri,
        $bodySha,
    ]);
    $signature = hash_hmac('sha256', $canonical, $secret);

    $request = Request::create($uri, $method, [], [], [], [], $body);
    $request->headers->add([
        'X-Sign-Key' => $key,
        'X-Sign-Ts' => (string) $fixedTime,
        'X-Sign-Nonce' => $nonce,
        'X-Signature' => $signature,
        'X-Sign-Sha256' => $bodySha,
    ]);

    config()->set("services.link.$key", [
        'secret' => $secret,
        'base_url' => 'test',
    ]);

    $verifier = new Verifier();
    $result = $verifier->verify($request);

    expect($result->key)->toBe($key);
});
