<?php

namespace App\Recipes;

use App\Recipes\Exceptions\InvalidResponseStatusException;
use App\Recipes\Exceptions\NoDownloadLinkException;
use Filament\Forms;
use Filament\Forms\Get;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class Direct extends Recipe
{
    use Concerns\GetsVersionFromPlugin;

    // TODO: add secrets to use in url

    /**
     * The name of the recipe.
     */
    public static function name(): string
    {
        return 'Direct';
    }

    /**
     * The form schema for the recipe.
     */
    public static function forms(): array
    {
        return [
            Forms\Components\TextInput::make('url')
                ->label('Url')
                ->required()
                ->live()
                ->helperText('The direct link to the package.'),
            Forms\Components\Section::make('URL parameters')
                ->schema(function (Get $get) {
                    $urlSecrets = self::urlSecrets($get('url'));

                    if (empty($urlSecrets)) {
                        return [];
                    }

                    return collect($urlSecrets)
                        ->map(function ($urlSecret) {
                            return Forms\Components\Select::make('secrets.'.$urlSecret)
                                ->label(Str::of($urlSecret)->title()->replace('_', ' '))
                                ->relationship(
                                    name: 'secrets',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn (Builder $query) => $query->where('type', $urlSecret),
                                )
                                ->native(false)
                                ->searchable()
                                ->preload()
                                // ->required() // This never validates correctly
                                ->live()
                                ->createOptionForm(function () use ($urlSecret) {
                                    return [
                                        Forms\Components\TextInput::make('name')
                                            ->label('Secret name')
                                            ->required()
                                            ->autofocus()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('value')
                                            ->password()
                                            ->revealable()
                                            ->required()
                                            ->formatStateUsing(fn (?string $state): ?string => filled($state) ? rescue(fn () => Crypt::decryptString($state), $state, false) : $state)
                                            ->dehydrateStateUsing(fn (string $state): string => Crypt::encryptString($state))
                                            ->dehydrated(fn (?string $state): bool => filled($state)),

                                        Forms\Components\TextInput::make('type') // Hidden input to save the value
                                            ->readOnly()
                                            ->default($urlSecret), // Ensure it has the correct default value
                                    ];
                                });
                        })
                        ->toArray();
                })
                ->columnSpan(2),
        ];
    }

    public static function urlSecrets(?string $url): ?array
    {
        if (! $url) {
            return [];
        }

        preg_match_all('/\${{([A-Za-z_]+)}}/', str_replace(' ', '', self::cleanUrl($url)), $matches);
        if (empty($matches[1])) {
            return [];
        }

        return $matches[1];
    }

    public static function cleanUrl(string $url): string
    {
        return str_replace(' ', '', $url);
    }

    public function replaceUrlSecrets(string $url): string
    {
        $replacements = [];
        $urlSecrets = static::urlSecrets($url);

        $cleanUrl = static::cleanUrl($url);

        if (! $urlSecrets) {
            return $cleanUrl;
        }

        foreach ($urlSecrets as $urlSecret) {
            $replacements['${{'.$urlSecret.'}}'] = $this->package->getSecret($urlSecret);
        }

        return strtr($cleanUrl, $replacements);
    }

    public static function urlSecretsValidationErrors(string $url): Collection
    {
        $errors = collect();
        $urlSecrets = self::urlSecrets($url);
        if ($urlSecrets) {
            foreach ($urlSecrets as $urlSecret) {
                if (! getenv($urlSecret)) {
                    $errors->push('Env. variable '.$urlSecret.' is required');
                }
            }
        }

        return $errors;
    }

    /**
     * Validate the license key.
     */
    public function licenseKeyError(): ?string
    {
        return null;
    }

    /**
     * Download the package using the JSON response.
     */
    private function getDownloadLinkFromJson($json): ?string
    {
        $packageDownloadLink = null;
        $possibleDownloadLinkKeys = [
            'download_link',
            'downloadLink',
            'download',
            'download_url',
            'url',
            'file',
            'package',
            'plugin',
            'theme',
        ];

        foreach ($possibleDownloadLinkKeys as $key) {
            if (isset($json[$key])) {
                $packageDownloadLink = $json[$key];
                break;
            }
        }

        if ($packageDownloadLink === null) {
            return null;
        }

        $downloadLink = $packageDownloadLink;

        return $downloadLink;
    }

    /**
     * Fetch the package information.
     */
    protected function fetchPackageInformation(): array
    {
        $url = $this->replaceUrlSecrets($this->package->settings['url']);

        $response = $this->httpClient::get($url);

        if (! $response->successful()) {
            throw new InvalidResponseStatusException($this);
        }

        if ($response->header('content-type') === 'application/json') {
            $downloadLink = $this->getDownloadLinkFromJson($response->json());
        } else {
            $downloadLink = $url;
        }

        if (! $downloadLink) {
            throw new NoDownloadLinkException($this);
        }

        return [
            'version' => $this->getVersionFromPlugin($downloadLink),
            'changelog' => '',
            'downloadLink' => $downloadLink,
        ];
    }
}
