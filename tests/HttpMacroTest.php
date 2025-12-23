<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

it('sends a request using the custom Http::user() macro', function (): void {
    config()->set('services.crm.outbound_password', 'secret123');
    config()->set('services.crm.endpoint', 'https://crm.example.com');

    Http::fake();

    Http::user('crm')->get('/login');

    Http::assertSent(fn ($request): bool => $request->url() === 'https://crm.example.com/login' &&
    $request->hasHeader('Authorization', 'Basic '.base64_encode('crm:secret123')) &&
    $request->method() === 'GET');
});
