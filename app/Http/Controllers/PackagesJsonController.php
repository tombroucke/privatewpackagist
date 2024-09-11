<?php

namespace App\Http\Controllers;

use App\PackagesJson;
use Illuminate\Support\Facades\Cache;

class PackagesJsonController extends Controller
{
    public function show()
    {
        $json = Cache::store('database')->get('packages.json');

        if (! $json) {
            $json = PackagesJson::regenerate();
        }

        return response()->json($json);
    }
}
