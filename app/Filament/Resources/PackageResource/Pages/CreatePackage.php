<?php

namespace App\Filament\Resources\PackageResource\Pages;

use App\Exceptions\ManualUpdaterCanNotUpdatePackages;
use App\Filament\Resources\PackageResource;
use App\Models\Package;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePackage extends CreateRecord
{
    protected static string $resource = PackageResource::class;

    protected function beforeCreate(): void
    {
        $tempPackage = new Package;
        $tempPackage->fill($this->data);

        $validationErrors = $tempPackage->validationErrors();
        if ($validationErrors->isNotEmpty()) {
            $validationErrors->each(function ($error) {
                Notification::make()
                    ->danger()
                    ->title('Validation Error')
                    ->body($error)
                    ->send();
            });

            $this->halt();
        }
    }

    protected function afterCreate(): void
    {
        try {
            $this->record->updater()->update();
        } catch (ManualUpdaterCanNotUpdatePackages $e) {
            // Do nothing, this is expected as manual updaters can not update packages
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body($e->getMessage())
                ->send();
        }
    }
}
