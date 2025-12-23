<?php

declare(strict_types=1);

use Foodieneers\Link\Signer;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

it('builds canonical string consistently', function (): void {
    $signer = new Signer();

    $canonical = $signer->canonical(
        ts: 1700000000,
        nonce: 'abc',
        method: 'POST',
        pathWithQuery: '/api/internal/messages?x=1',
        bodySha256: 'deadbeef',
    );

    expect($canonical)->toBe("1700000000\nabc\nPOST\n/api/internal/messages?x=1\ndeadbeef");
});

it('adds Link signing headers to outgoing request', function () {

    config()->set('services.link.chat_b', [
        'base_url' => 'https://example.test',
        'secret' => 'super-secret',
    ]);

    Http::fake();

    Http::link('chat_b')->post('/api/internal/ping', ['hello' => 'world']);

    Http::assertSent(fn (Request $request): bool => $request->hasHeader('X-Sign-Key') &&
           $request->header('X-Sign-Key')[0] === 'chat_a' &&
           $request->url() === 'https://example.test/api/internal/ping');
});

it('throws if Link is not configured', function () {
    config()->set('services.link', []);

    expect(fn () => Http::link('missing')->get('/ping'))
        ->toThrow(InvalidArgumentException::class, 'not configured');
});

it('throws if Link config is missing required fields', function () {
    config()->set('services.link.chat_b', [
        'base_url' => 'https://example.test',
        'secret' => 's',
    ]);

    expect(fn () => Http::link('chat_b')->get('/ping'))
        ->toThrow(InvalidArgumentException::class);
});

it('uses baseUrl from config and trims trailing slash', function () {
    config()->set('services.link.chat_b', [
        'base_url' => 'https://example.test/', // trailing slash
        'secret' => 'super-secret',
    ]);

    mock(Signer::class)
        ->shouldReceive('headersFor')
        ->andReturn([]);

    Http::fake(function (Request $request) {
        expect($request->url())->toBe('https://example.test/api/internal/ping');

        return Http::response(['ok' => true], 200);
    });

    Http::link('chat_b')->post('/api/internal/ping', []);
});
