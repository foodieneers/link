<?php

use function Pest\Laravel\mock;
use Illuminate\Http\Client\Request;
use Foodieneers\Bridge\BridgeSigner;
use Illuminate\Support\Facades\Http;
use Foodieneers\Bridge\RegisterService;
use Psr\Http\Message\RequestInterface;

beforeEach(function () {
    (new RegisterService())();

    Http::preventStrayRequests();
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


it('adds bridge signing headers to outgoing request', function () {
    config()->set('services.bridge.chat_b', [
        'base_url' => 'https://example.test',
        'key' => 'chat_a',
        'secret' => 'super-secret',
    ]);

    mock(BridgeSigner::class)
        ->shouldReceive('headersFor')
        ->once()
        // Ensure the type-hint here matches the PSR interface used in your middleware
        ->withArgs(function ($req, string $key, string $secret) {
            return $req instanceof RequestInterface 
                && $key === 'chat_a' 
                && $secret === 'super-secret';
        })
        ->andReturn([
            'X-Sign-Key' => 'chat_a',
            'X-Signature' => 'abc123',
        ]);

Http::fake(function (Request $request) {

    expect($request->hasHeader('X-Sign-Key'))->toBeTrue();
    return Http::response(['ok' => true], 200);
});
    $res = Http::bridge('chat_b')->post('/api/internal/ping', ['hello' => 'world']);

    //$res->assertOk()->assertJson(['ok' => true]);
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
