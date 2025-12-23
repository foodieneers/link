<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Foodieneers\Bridge\Http\Controllers\UpController;

beforeEach(function (): void {
    Route::get('/test', UpController::class);
});

it('sends OK status')->get('/test')->assertOk();
