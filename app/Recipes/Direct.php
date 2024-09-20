<?php

namespace App\Recipes;

use App\Recipes\Exceptions\InvalidResponseStatusException;
use App\Recipes\Exceptions\NoDownloadLinkException;
use Filament\Forms;

class Direct extends Recipe
{
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
                ->helperText('The direct link to the package. You can use ${{ YOUR_VAR }} as a placeholder for environment variables. Note that the environment variables must be prefixed with the package prefix.'),
        ];
    }

    /**
     * Download the package using the JSON response.
     */
    private function downloadPackageFromJson($json): ?array
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
        $fileContent = $this->httpClient::get($packageDownloadLink)->body();

        return [$fileContent, $downloadLink];
    }

    /**
     * Extract the version from the plugin.
     */
    private function extractVersionFromPlugin($tmpDir): ?string
    {
        if (! is_dir($tmpDir)) {
            return null;
        }

        $searchFiles = glob($tmpDir.'/*.{php,css}', GLOB_BRACE);

        if (empty($searchFiles)) {
            return null;
        }
        $pluginName = basename($tmpDir);
        $supposedMainPluginFile = $tmpDir.'/'.$pluginName.'.php';

        if (! file_exists($supposedMainPluginFile)) {
            $supposedMainPluginFile = null;
        }

        if ($supposedMainPluginFile) {
            unset($searchFiles[array_search($supposedMainPluginFile, $searchFiles)]);
            array_unshift($searchFiles, $supposedMainPluginFile);
        }

        foreach ($searchFiles as $file) {
            $version = $this->maybeGetPluginVersionFromFile($file);

            if ($version) {
                return $version;
            }
        }

        return null;
    }

    /**
     * Maybe get the plugin version from the file.
     */
    private function maybeGetPluginVersionFromFile($file): ?string
    {
        $fileContents = file_get_contents($file, false, null, 0, 8192);

        if ($fileContents === false) {
            return null;
        }

        if (preg_match('/^[ \t\/*#@]*Version:(.*)$/mi', $fileContents, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Fetch the package information.
     */
    protected function fetchPackageInformation(): array
    {
        $response = $this->httpClient::get($this->package->settings['url']);

        if (! $response->successful()) {
            throw new InvalidResponseStatusException($this);
        }

        if ($response->header('content-type') === 'application/json') {
            [$fileContent, $downloadLink] = $this->downloadPackageFromJson($response->json());
        } else {
            $fileContent = $response->body();
            $downloadLink = $this->package->settings['url'];
        }

        if (! $fileContent) {
            throw new NoDownloadLinkException($this);
        }

        if (substr($fileContent, 0, 2) !== 'PK') {
            throw new \Exception('The file is not a zip file');
        }

        $tempFileName = tempnam(sys_get_temp_dir(), 'plugin_'.$this->package->slug.'_').'.zip';
        $tempPackageDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.$this->package->slug;

        file_put_contents($tempFileName, $fileContent);
        $zip = new \ZipArchive;
        $res = $zip->open($tempFileName);
        if ($res === true) {
            $zip->extractTo($tempPackageDirectory);
            $zip->close();
        }

        $tempPluginDirectoryName = collect(scandir($tempPackageDirectory))
            ->filter(fn ($file) => ! in_array($file, ['.', '..']))
            ->first();

        $tempPluginDirectory = $tempPackageDirectory.DIRECTORY_SEPARATOR.$tempPluginDirectoryName;
        unlink($tempFileName);

        return [
            'version' => $this->extractVersionFromPlugin($tempPluginDirectory),
            'changelog' => '',
            'downloadLink' => $downloadLink,
        ];
    }
}
