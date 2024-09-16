<?php

namespace App\Http\Controllers;

use App\PackagesCache;

class PackagesJsonController extends Controller
{
    public function show()
    {
        $json = app()->make(PackagesCache::class)->get();

        return response()
            ->json($json)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
