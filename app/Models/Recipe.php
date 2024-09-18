<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class Recipe extends Model
{
    use Sushi;

    /**
     * Retrieve the rows.
     */
    public function getRows()
    {
        return app('recipes')->map(fn ($recipe) => [
            'name' => $recipe::name(),
            'options' => count($recipe::forms()) - count($recipe::secrets()),
            'secrets' => count($recipe::secrets()),
            'packages' => Package::where('recipe', $recipe::slug())->count(),
        ])->values()->all();
    }
}
