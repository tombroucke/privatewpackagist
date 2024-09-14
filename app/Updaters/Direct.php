<?php

namespace App\Updaters;

use App\Exceptions\DownloadLinkNotSetException;
use App\Models\Release;
use App\PackageDownloader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Direct extends Abstracts\Updater implements Contracts\Updater
{
    public function fetchTitle(): string
    {

        $name = Str::of($this->package->slug)
            ->title()
            ->replace('-', ' ')
            ->__toString();

        return strip_tags($name);
    }

    public function validationErrors(): Collection
    {
        $errors = new Collection;

        $environmentVariables = $this->environmentVariables();
        if ($environmentVariables) {
            foreach ($environmentVariables as $environmentVariable) {
                if (! getenv($environmentVariable)) {
                    $errors->push('Env. variable '.$environmentVariable.' is required');
                }
            }
        }

        return $errors;
    }

    private function environmentVariables(): ?array
    {
        preg_match_all('/\${{([A-Za-z_]+)}}/', str_replace(' ', '', $this->cleanUrl()), $matches);

        if (empty($matches[1])) {
            return null;
        }

        return array_map(function ($match) {
            $match = preg_replace('/^'.$this->package->prefix().'/', '', $match);

            return $this->package->prefixedEnvironmentVariable($match);
        }, $matches[1]);

        return $matches[1];
    }

    private function cleanUrl(): string
    {
        return str_replace(' ', '', $this->package->settings['url']);
    }

    public function update(): ?Release
    {
        $this->setDirectPackageInformation();

        return parent::update();

    }

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
        $fileContent = Http::get($packageDownloadLink)->body();

        return [$fileContent, $downloadLink];
    }

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

    public function testDownload(): bool
    {
        $this->setDirectPackageInformation();

        return (new PackageDownloader($this))
            ->test();
    }

    protected function packageInformation(): array
    {
        return [null, null, ''];
    }

    private function setDirectPackageInformation(): void
    {

        $replacements = [];
        $environmentVariables = $this->environmentVariables();

        if ($environmentVariables) {
            foreach ($environmentVariables as $environmentVariable) {
                $replacements['${{'.$environmentVariable.'}}'] = getenv($environmentVariable);
            }
        }

        $downloadLink = strtr($this->cleanUrl(), $replacements);
        $response = Http::get($downloadLink);

        if (! $response->successful()) {
            throw new \Exception('Failed to download the plugin');
        }

        if ($response->header('content-type') === 'application/json') {
            [$fileContent, $downloadLink] = $this->downloadPackageFromJson($response->json());
        } else {
            $fileContent = $response->body();
        }

        if (! $fileContent) {
            throw new DownloadLinkNotSetException;
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

        $this->version = $this->extractVersionFromPlugin($tempPluginDirectory);
        $this->downloadLink = $downloadLink;
        $this->changelog = '';
    }
}