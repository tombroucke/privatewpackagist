<?php

namespace App\Filament\Resources\PackageResource\Widgets;

use App\Models\Package;
use Filament\Widgets\Widget;

class PackageLicenseStatus extends Widget
{
    public ?Package $record = null;

    protected int|string|array $columnSpan = 2;

    protected static string $view = 'filament.resources.package-resource.widgets.package-license-status';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $package = $this->record;
        $licenseValidFrom = $package->license_valid_from;
        $licenseValidTo = $package->license_valid_to;
        $licenseValid = $package->valid;

        return [
            'licenseValid' => $licenseValid,
            'licenseValidFrom' => $licenseValidFrom,
            'licenseValidTo' => $licenseValidTo,
        ];
    }
}
