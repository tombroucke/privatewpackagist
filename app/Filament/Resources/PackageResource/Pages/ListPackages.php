<?php

namespace App\Filament\Resources\PackageResource\Pages;

use Filament\Actions;
use App\Models\Package;
use App\Observers\PackageObserver;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\PackageResource;
use App\Filament\Resources\PackageResource\Widgets;

class ListPackages extends ListRecords
{
    /**
     * The resource this page belongs to.
     */
    protected static string $resource = PackageResource::class;

    /**
     * Get the header actions for the page.
     *
     * @return array<Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->successRedirectUrl(fn (Package $package): string => route('filament.admin.resources.packages.edit', [
                    'record' => $package,
                ]))
                ->after(function (Package $package) {
                    $errors = $package->validationErrors();

                    if ($errors->isNotEmpty()) {
                        $errors->each(fn ($error) => Notification::make()
                            ->danger()
                            ->title('Validation Error')
                            ->body($error)
                            ->send()
                        );
                    } else {
                        if (is_null($package->license_valid_from)) {
                            $package->license_valid_from = now();
                            $package->saveQuietly();
                        }
                        PackageObserver::createRelease($package);
                    }
                }),
        ];
    }

    /**
     * The header widgets.
     */
    protected function getHeaderWidgets(): array
    {
        return [
            Widgets\PackageOverview::class,
        ];
    }
}
