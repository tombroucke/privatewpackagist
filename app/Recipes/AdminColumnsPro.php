<?php

namespace App\Recipes;

use App\Recipes\Exceptions\NoDownloadLinkException;
use App\Recipes\Exceptions\UnexpectedResponseException;
use Filament\Forms;

class AdminColumnsPro extends Recipe
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
        return 'Admin Columns Pro';
    }

    /**
     * The form schema for the recipe.
     */
    public static function forms(): array
    {
        return [
            Forms\Components\Select::make('package')
                ->options([
                    'admin-columns-pro' => 'Admin Columns Pro',
                    'ac-addon-acf' => 'ACF Add-on',
                    'ac-addon-buddypress' => 'BuddyPress Add-on',
                    'ac-addon-events-calendar' => 'Events Calendar Add-on',
                    'ac-addon-gravity-forms' => 'Gravity Forms Add-on',
                    'ac-addon-jetengine' => 'JetEngine Add-on',
                    'ac-addon-metabox' => 'Meta Box Add-on',
                    'ac-addon-ninjaforms' => 'Ninja Forms Add-on',
                    'ac-addon-pods' => 'Pods Add-on',
                    'ac-addon-types' => 'Toolset Types Add-on',
                    'ac-addon-woocommerce' => 'WooCommerce Add-on',
                    'ac-addon-yoast-seo' => 'Yoast SEO Add-on',
                ])
                ->default('admin-columns-pro')
                ->searchable()
                ->required(),

            Forms\Components\TextInput::make('source_url')
                ->label('Source URL')
                ->url()
                ->required(),
        ];
    }

    /**
     * Validate the license key.
     */
    public function licenseKeyError(): ?string
    {
        // This should be implemented to validate the license key, but this would require for the activation_key to be stored and shared between all the ACP recipes.
        // $response = $this->doRequest([
        //     'command' => 'subscription_details',
        //     'activation_url' => $this->package->settings['source_url'],
        //     'activation_key' => '{{activation_key}}',
        //     'meta' => [
        //         'admin-columns-pro' => '5.7.4',
        //         'php_version' => PHP_VERSION,
        //         'acp_version' => '5.7.4',
        //         'is_network' => false,
        //         'ip' => '127.0.0.1',
        //     ],
        // ]);

        // Instead we will just check if we can get the latest version of the package and return a generic error message if not.
        $response = $this->doRequest([
            'command' => 'products_update',
            'plugin_name' => $this->package->settings['slug'],
            'subscription_key' => $this->package->getSecret('license_key'),
        ]);

        $active = ($response['code'] ?? null) !== 'error';
        $message = isset($response['message']) ? 'Answer from remote server: '.$response['message'] : 'Invalid license key.';

        return $active ? null : $message;
    }

    public function activateLicenseKey(): array
    {
        $response = $this->doRequest([
            'command' => 'activate',
            'activation_url' => $this->package->settings['source_url'],
            'subscription_key' => $this->package->getSecret('license_key'),
        ]);

        $return = [
            'activated' => $response['activated'] ?? false,
            'message' => $response['message'] ?? null,
            'activation_key' => $response['activation_key'] ?? null,
        ];

        return $return;
    }

    /**
     * Fetch the package information.
     */
    protected function fetchPackageInformation(): array
    {
        [$downloadLink, $changelog] = $this->getDownloadLinkAndChangelog();

        return [
            'version' => $this->getVersion(),
            'changelog' => $changelog,
            'downloadLink' => $downloadLink,
        ];
    }

    /**
     * Get the download link and changelog.
     */
    private function getDownloadLinkAndChangelog(): array
    {
        $response = $this->doRequest([
            'command' => 'products_update',
            'plugin_name' => $this->package->settings['slug'],
            'subscription_key' => $this->package->getSecret('license_key'),
        ]);

        if (! isset($response['admin-columns-pro']['package'])) {
            throw new NoDownloadLinkException($this);
        }

        return [$response['admin-columns-pro']['package'], ($response['admin-columns-pro']['sections']['changelog'] ?? '')];
    }

    /**
     * Retrieve the latest version of the package.
     */
    private function getVersion(): string
    {
        $response = $this->doRequest([
            'command' => 'product_information',
            'plugin_name' => $this->package->settings['slug'],
        ]);

        if (! isset($response['version'])) {
            throw new UnexpectedResponseException($this);
        }

        return $response['version'];
    }

    /**
     * Perform a request to the Admin Columns API.
     */
    private function doRequest(array $args)
    {
        return $this->httpClient::asForm()
            ->withUserAgent($this->userAgent())
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
            ->post('https://www.admincolumns.com', $args)
            ->json();
    }
}
