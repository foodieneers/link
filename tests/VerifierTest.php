<?php

declare(strict_types=1);

use Foodieneers\Bridge\Verifier;
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
        'X-Bridge-Key' => $key,
        'X-Bridge-Timestamp' => (string) $fixedTime,
        'X-Bridge-Nonce' => $nonce,
        'X-Bridge-Signature' => $signature,
        'X-Bridge-Body-Hash' => $bodySha,
    ]);

    config()->set("services.bridge.$key.secret", $secret);

    $verifier = new Verifier();

    test()->travelTo(DateTimeImmutable::createFromFormat('U', (string) $fixedTime));

    $result = $verifier->verify($request);

    expect($result->key)->toBe($key);
});
