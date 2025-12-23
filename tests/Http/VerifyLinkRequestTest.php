<?php

declare(strict_types=1);

use Foodieneers\Link\Exceptions\BadRequestException;
use Foodieneers\Link\Http\Middleware\VerifyLinkRequest;
use Foodieneers\Link\VerificationResult;
use Foodieneers\Link\Verifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\mock;
use function Pest\Laravel\postJson;

beforeEach(function () {
    Route::middleware(VerifyLinkRequest::class)
        ->post('/_test/link', fn (Request $request) => 
        response()->json([
            'ok' => true,
            'link_key' => $request->attributes->get('link.key'),
        ]));
});

it('returns 400 when verifier throws BadRequestException', function () {
    mock(Verifier::class)
        ->shouldReceive('verify')
        ->once()
        ->andThrow(new BadRequestException('Missing headers'));

    postJson('/_test/link', ['x' => 1])
        ->assertStatus(400)
        ->assertJson([
            'message' => 'Bad Request',
        ]);
});

it('passes through when verifier succeeds and sets link.key attribute', function () {
    mock(Verifier::class)
        ->shouldReceive('verify')
        ->once()
        ->andReturn(new VerificationResult('key', 1, '1'));

    postJson('/_test/link', ['x' => 1])
        ->assertOk()
        ->assertJson([
            'ok' => true,
            'link_key' => 'key',
        ]);
});
