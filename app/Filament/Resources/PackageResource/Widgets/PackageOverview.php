<?php

namespace App\Filament\Resources\PackageResource\Widgets;

use App\Models\Package;
use App\Models\Release;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class PackageOverview extends BaseWidget
{
    /**
     * The widget stats.
     */
    protected function getStats(): array
    {
        $packages = Package::count();
        $releases = Release::count();
        $latest = Release::latest()->first();

        return [
            Stat::make('Total Packages', Number::format($packages))
                ->description('The packages available for download.')
                ->icon('heroicon-o-archive-box'),

            Stat::make('Total Releases', Number::format($releases))
                ->description('The total number of versions released.')
                ->icon('heroicon-o-tag'),

            Stat::make('Last Updated', $latest->created_at->diffForHumans())
                ->description('The last time a release was created.')
                ->icon('heroicon-o-calendar'),
        ];
    }
}
