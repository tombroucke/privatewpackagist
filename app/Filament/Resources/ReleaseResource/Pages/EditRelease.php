<?php

namespace App\Filament\Resources\ReleaseResource\Pages;

use App\Filament\Resources\ReleaseResource;
use App\Models\Package;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EditRelease extends EditRecord
{
    protected static string $resource = ReleaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['path'] = 'packages/'.$data['path'];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = $this->moveUpload($data);

        return $data;
    }

    private function moveUpload($data)
    {
        if (! isset($data['path'])) {
            return $data;
        }

        if ($data['path'] instanceof TemporaryUploadedFile) {
            $package = Package::findOrFail($data['package_id']);

            $path = $package->generateReleasePath($data['version']);

            $data['path']->storeAs('packages', $path);
            $data['path'] = $path;
        } else {
            $data['path'] = preg_replace('/^packages\//', '', $data['path']);
        }

        return $data;
    }
}
