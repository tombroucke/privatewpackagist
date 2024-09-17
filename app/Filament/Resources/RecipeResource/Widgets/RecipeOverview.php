<?php

namespace App\Filament\Resources\RecipeResource\Widgets;

use App\Models\Package;
use App\Models\Recipe;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class RecipeOverview extends BaseWidget
{
    /**
     * The widget stats.
     */
    protected function getStats(): array
    {
        $recipes = Recipe::all();

        $popular = $recipes->isNotEmpty()
            ? $recipes->sortByDesc('packages')->first()->name
            : 'N/A';

        $packages = Package::count();

        return [
            Stat::make('Total Recipes', Number::format($recipes->count()))
                ->description('The total number of available recipes.')
                ->icon('heroicon-o-light-bulb'),

            Stat::make('Popular Recipe', Str::limit($popular, 15))
                ->description('The most commonly used recipe.')
                ->icon('heroicon-o-star'),

            Stat::make('Packages', Number::format($packages))
                ->description('The total number of packages.')
                ->icon('heroicon-o-archive-box'),
        ];
    }
}
