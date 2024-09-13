<?php

namespace App\Http\Controllers;

use App\PackagesJson;
use Illuminate\Support\Facades\Cache;

class PackagesJsonController extends Controller
{
    public function show()
    {
        $json = Cache::get('packages.json');
        if (! $json) {
            $json = app()->make(PackagesJson::class)->regenerate();
        }

        return response()
            ->json($json)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
