<?php

namespace App\Http\Controllers;

use App\PackagesCache;
use Illuminate\Http\JsonResponse;

class PackagesJsonController extends Controller
{
    /**
     * Show the packages JSON.
     */
    public function show(): JsonResponse
    {
        $packages = app()->make(PackagesCache::class)->get();

        return response()
            ->json($packages)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
