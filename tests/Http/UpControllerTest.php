<?php

declare(strict_types=1);

use Foodieneers\Link\Http\Controllers\UpController;
use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    Route::get('/test', UpController::class);
});

it('sends OK status')->get('/test')->assertOk();
