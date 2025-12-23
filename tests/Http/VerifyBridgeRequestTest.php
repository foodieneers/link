<?php

declare(strict_types=1);

use Foodieneers\Bridge\Exceptions\BadRequestException;
use Foodieneers\Bridge\Http\Middleware\VerifyBridgeRequest;
use Foodieneers\Bridge\VerificationResult;
use Foodieneers\Bridge\Verifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\mock;
use function Pest\Laravel\postJson;

beforeEach(function () {
    Route::middleware(VerifyBridgeRequest::class)
        ->post('/_test/bridge', fn (Request $request) => response()->json([
            'ok' => true,
            'bridge_key' => $request->attributes->get('bridge.key'),
        ]));
});

it('returns 400 when verifier throws BadRequestException', function () {
    mock(Verifier::class)
        ->shouldReceive('verify')
        ->once()
        ->andThrow(new BadRequestException('Missing headers'));

    postJson('/_test/bridge', ['x' => 1])
        ->assertStatus(400)
        ->assertJson([
            'message' => 'Bad Request',
        ]);
});

it('passes through when verifier succeeds and sets bridge.key attribute', function () {
    mock(Verifier::class)
        ->shouldReceive('verify')
        ->once()
        ->andReturn(new VerificationResult('key', 1, 1));

    postJson('/_test/bridge', ['x' => 1])
        ->assertOk()
        ->assertJson([
            'ok' => true,
            'bridge_key' => 'key',
        ]);
});
