<?php

namespace App\Recipes;

use App\Recipes\Exceptions\LicenseCheckFailedException;
use Filament\Forms;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Edd extends Recipe
{
    /**
     * The secrets used by the recipe.
     */
    protected static array $secrets = [
        'license_key',
    ];

    /**
     * The name of the recipe.
     */
    public static function name(): string
    {
        return 'Easy Digital Downloads';
    }

    /**
     * The form schema for the recipe.
     */
    public static function forms(): array
    {
        return [
            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->required(),

            Forms\Components\TextInput::make('source_url')
                ->label('Source URL')
                ->url()
                ->required(),

            Forms\Components\TextInput::make('endpoint_url')
                ->label('Endpoint URL')
                ->url()
                ->required(),

            Forms\Components\Select::make('method')
                ->label('Method')
                ->options([
                    'GET' => 'GET',
                    'POST' => 'POST',
                ])
                ->searchable()
                ->required(),

            Forms\Components\TextInput::make('license_key')
                ->required(),

            Forms\Components\TextInput::make('changelog_extract')
                ->label('Changelog extract')
                ->helperText('Regular expression to extract changelog'),

            Forms\Components\Checkbox::make('skip_license_check')
                ->label('Skip license check')
                ->helperText('Some plugins like WP All Import does not return a valid license key. Only tick this box if you get a \'403 Invalid license\' error'),
        ];
    }

    /**
     * The package title.
     */
    public function fetchPackageTitle(): string
    {
        $response = $this->doEddAction('check_license');

        return $response['item_name'] ?? $response['name'] ?? Str::of($this->package->slug)
            ->title()
            ->replace('-', ' ')
            ->stripTags();
    }

    /**
     * The validation errors for the recipe.
     */
    public function validationErrors(): Collection
    {
        $errors = new Collection;

        $this->activateLicense();

        if (! $this->package->settings['skip_license_check'] && ! $this->checkLicense()) {
            $errors->push('Invalid license');
        }

        return $errors;
    }

    /**
     * Activate the license.
     */
    private function activateLicense(): void
    {
        $this->doEddAction('activate_license');
    }

    /**
     * The user agent for the request.
     */
    public function userAgent(): string
    {
        return sprintf('%s; %s',
            config('packagist.user_agent'),
            $this->package->settings['source_url'],
        );
    }

    /**
     * Check the license.
     */
    private function checkLicense(): bool
    {
        $response = $this->doEddAction('check_license');

        return isset($response['license']) && $response['license'] === 'valid';
    }

    /**
     * Handle the request.
     */
    private function doEddAction(string $action, string $method = 'GET'): array
    {

        $args = [
            'edd_action' => $action,
            'license' => $this->package->secrets()->get('license_key'),
            'item_name' => $this->package->settings['slug'],
            'url' => $this->package->settings['source_url'],
        ];

        $request = $this->httpClient::withUserAgent($this->userAgent());

        if ($method === 'POST') {
            $response = $request
                ->asForm()
                ->post($this->package->settings['endpoint_url'], $args);
        } else {
            $response = $request
                ->withQueryParameters($args)
                ->get($this->package->settings['endpoint_url']);
        }

        $body = $response->body();

        return json_decode($body, true);
    }

    /**
     * Fetch the package information.
     */
    protected function fetchPackageInformation(): array
    {
        if (! $this->package->settings['skip_license_check'] && ! $this->checkLicense()) {
            throw new LicenseCheckFailedException($this);
        }

        $response = $this->doEddAction('get_version', $this->package->settings['method']);

        $version = $response['new_version'];
        $sections = @unserialize($response['sections']);

        $pattern = $this->package->settings['changelog_extract'] ?? '\*\*(\d+\.\d+\.\d+) \((.*?)\)\*\*\n(.*?)\n\n';

        return [
            'version' => $version,
            'changelog' => $this->extractLatestChangelog($sections['changelog'] ?? '', $pattern),
            'downloadLink' => $response['download_link'],
        ];
    }
}
