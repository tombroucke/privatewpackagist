<?php

namespace App\Recipes\Concerns;

use App\Recipes\Exceptions\NoDownloadLinkException;

trait GetsVersionFromPlugin
{
    /**
     * Extract the version from the plugin.
     */
    private function getVersionFromPlugin($packageDownloadLink): ?string
    {
        $fileContent = $this->httpClient::withUserAgent($this->userAgent())->get($packageDownloadLink)->body();
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

        if (! is_dir($tempPluginDirectory)) {
            return null;
        }

        $searchFiles = glob($tempPluginDirectory.'/*.{php,css}', GLOB_BRACE);

        if (empty($searchFiles)) {
            return null;
        }
        $pluginName = basename($tempPluginDirectory);
        $supposedMainPluginFile = $tempPluginDirectory.'/'.$pluginName.'.php';

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
}
