<?php

namespace App\Updaters\Concerns;

use Illuminate\Support\Collection;

trait EnvironmentVariablesInUrl
{
    private function environmentVariables(string $url): ?array
    {
        preg_match_all('/\${{([A-Za-z_]+)}}/', str_replace(' ', '', $this->cleanUrl($url)), $matches);

        if (empty($matches[1])) {
            return null;
        }

        return array_map(function ($match) {
            $match = preg_replace('/^'.$this->package->prefix().'/', '', $match);

            return $this->package->prefixedEnvironmentVariable($match);
        }, $matches[1]);

        return $matches[1];
    }

    private function cleanUrl(string $url): string
    {
        return str_replace(' ', '', $url);
    }

    private function replaceEnvironmentVariables(string $url): string
    {
        $replacements = [];
        $environmentVariables = $this->environmentVariables($url);

        $cleanUrl = $this->cleanUrl($url);

        if (! $environmentVariables) {
            return $cleanUrl;
        }

        foreach ($environmentVariables as $environmentVariable) {
            $replacements['${{'.$environmentVariable.'}}'] = getenv($environmentVariable);
        }

        return strtr($cleanUrl, $replacements);
    }

    private function environmentVariablesValidationErrors(string $url): Collection
    {
        $errors = collect();
        $environmentVariables = $this->environmentVariables($url);
        if ($environmentVariables) {
            foreach ($environmentVariables as $environmentVariable) {
                if (! getenv($environmentVariable)) {
                    $errors->push('Env. variable '.$environmentVariable.' is required');
                }
            }
        }

        return $errors;
    }
}
