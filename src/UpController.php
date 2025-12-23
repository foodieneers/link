<?php

declare(strict_types=1);

namespace Foodieneers\ApiAuth;

use Illuminate\Routing\Controller;

final class UpController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        return response()->json(['status' => 'ok']);
    }
}
