<?php

namespace App\Filament\Resources\ReleaseResource\Pages;

use App\Filament\Resources\ReleaseResource;
use App\Models\Package;
use Filament\Resources\Pages\CreateRecord;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CreateRelease extends CreateRecord
{
    protected static string $resource = ReleaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->moveUpload($data);

        return $data;
    }

    private function moveUpload($data)
    {
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
