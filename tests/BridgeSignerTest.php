<?php

declare(strict_types=1);

use Foodieneers\Bridge\BridgeSigner;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\RequestInterface;

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

    Http::assertSent(function (Request $request) {
        return $request->hasHeader('X-Sign-Key') &&
               $request->header('X-Sign-Key')[0] === 'chat_a' &&
               $request->url() === 'https://example.test/api/internal/ping';
    });
});