<?php

namespace App\Filament\Resources\PackageResource\Pages;

use App\Filament\Resources\PackageResource;
use App\Filament\Resources\PackageResource\Widgets;
use App\Models\Package;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

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
                ->before(function (CreateAction $action, array $data) {
                    $errors = (new Package($data))->validationErrors();

                    if ($errors->isNotEmpty()) {
                        $errors->each(fn ($error) => Notification::make()
                            ->danger()
                            ->title('Validation Error')
                            ->body($error)
                            ->send()
                        );
                        $this->halt();
                    }
                })
                ->successRedirectUrl(fn (Package $package): string => route('filament.admin.resources.packages.edit', [
                    'record' => $package,
                ])),
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
