<?php

declare(strict_types=1);

use Foodieneers\Bridge\BridgeSigner;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

it('builds canonical string consistently', function (): void {
    $signer = new BridgeSigner();

    $canonical = $signer->canonical(
        ts: 1700000000,
        nonce: 'abc',
        method: 'POST',
        pathWithQuery: '/api/internal/messages?x=1',
        bodySha256: 'deadbeef',
    );

    expect($canonical)->toBe("1700000000\nabc\nPOST\n/api/internal/messages?x=1\ndeadbeef");
});

it('adds bridge signing headers to outgoing request', function () {

    config()->set('services.bridge.chat_b', [
        'base_url' => 'https://example.test',
        'key' => 'chat_a',
        'secret' => 'super-secret',
    ]);

    Http::fake();

    Http::bridge('chat_b')->post('/api/internal/ping', ['hello' => 'world']);

    Http::assertSent(fn (Request $request): bool => $request->hasHeader('X-Sign-Key') &&
           $request->header('X-Sign-Key')[0] === 'chat_a' &&
           $request->url() === 'https://example.test/api/internal/ping');
});

it('throws if bridge is not configured', function () {
    config()->set('services.bridge', []);

    expect(fn () => Http::bridge('missing')->get('/ping'))
        ->toThrow(InvalidArgumentException::class, 'not configured');
});

it('throws if bridge config is missing required fields', function () {
    config()->set('services.bridge.chat_b', [
        'base_url' => 'https://example.test',
        'key' => '',
        'secret' => 's',
    ]);

    expect(fn () => Http::bridge('chat_b')->get('/ping'))
        ->toThrow(InvalidArgumentException::class, 'missing base_url and/or signing.key/signing.secret');
});

it('uses baseUrl from config and trims trailing slash', function () {
    config()->set('services.bridge.chat_b', [
        'base_url' => 'https://example.test/', // trailing slash
        'key' => 'chat_a',
        'secret' => 'super-secret',
    ]);

    mock(BridgeSigner::class)
        ->shouldReceive('headersFor')
        ->andReturn([]);

    Http::fake(function (Request $request) {
        expect($request->url())->toBe('https://example.test/api/internal/ping');

        return Http::response(['ok' => true], 200);
    });

    Http::bridge('chat_b')->post('/api/internal/ping', []);
});
