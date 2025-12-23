<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    Route::get('/test-protected', fn () => response()->json(['message' => 'Authorized']))->middleware('api.auth:myuser');
    config()->set('services.myuser.inbound_password', 'secret123');
});

use function Pest\Laravel\get;

it('denies access without basic auth', function (): void {
    get('/test-protected')
        ->assertStatus(401)
        ->assertSee('Unauthorized');
});

it('denies access with wrong credentials', function (): void {
    get('/test-protected', [
        'Authorization' => 'Basic '.base64_encode('wronguser:wrongpass'),
    ])
        ->assertStatus(401)
        ->assertSee('Unauthorized');
});

it('allows access with correct credentials', function (): void {
    $username = 'myuser';
    $password = config("services.{$username}.inbound_password");

    get('/test-protected', [
        'Authorization' => 'Basic '.base64_encode("{$username}:{$password}"),
    ])
        ->assertStatus(200)
        ->assertJson(['message' => 'Authorized']);
});
