<?php

namespace App\Filament\Resources\ReleaseResource\Widgets;

use App\Models\Release;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class ReleaseOverview extends BaseWidget
{
    /**
     * The widget stats.
     */
    protected function getStats(): array
    {
        $releases = Release::count();
        $latest = Release::latest()->first();

        return [
            Stat::make('Total Releases', Number::format($releases))
                ->description('The total number of versions released.')
                ->icon('heroicon-o-tag'),

            Stat::make('Latest Release', $latest->package->name)
                ->description('The last package to have a release.')
                ->icon('heroicon-o-tag'),

            Stat::make('Last Updated', $latest->created_at->diffForHumans())
                ->description('The last time a package was updated.')
                ->icon('heroicon-o-clock'),
        ];
    }
}
