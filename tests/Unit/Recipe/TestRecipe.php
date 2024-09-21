<?php

namespace Tests\Unit\Recipe;

use App\Models\Package;
use App\Recipes\Recipe as AbstractRecipe;
use Filament\Forms;
use Illuminate\Support\Facades\Http;

class TestRecipe extends AbstractRecipe
{
    protected static array $secrets = [
        'license_key',
    ];

    public static function forms(): array
    {
        return [
            Forms\Components\TextInput::make('license_key')
                ->required(),
        ];
    }

    public function __construct(protected Package $package, protected Http $httpClient)
    {
        parent::__construct($package, $httpClient);
    }

    public function fetchPackageInformation(): array
    {
        return [
            'version' => '1.0.0',
            'changelog' => '',
            'downloadLink' => 'https://example.com/download',
        ];
    }

    public static function name(): string
    {
        return 'Test recipe';
    }

    public function licenseKeyError(): ?string
    {
        if ($this->package->secrets()->get('license_key') !== 'test_license_key') {
            return 'Invalid license key';
        }

        return null;
    }
}
