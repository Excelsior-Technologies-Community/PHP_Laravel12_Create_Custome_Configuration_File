<?php

namespace App\Services;

class EnvWriter
{
    protected string $envPath;

    public function __construct()
    {
        $this->envPath = base_path('.env');
    }

    public function set(string $key, ?string $value): bool
    {
        if (!file_exists($this->envPath)) {
            return false;
        }

        $content = file_get_contents($this->envPath);

        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

        $newLine = $value !== null ? "{$key}={$value}" : "{$key}=";

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $newLine, $content);
        } else {
            $content = rtrim($content) . "\n" . $newLine . "\n";
        }

        return file_put_contents($this->envPath, $content) !== false;
    }

    public function setMultiple(array $values): bool
    {
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value)) {
                return false;
            }
        }

        return true;
    }

    public function get(string $key): ?string
    {
        if (!file_exists($this->envPath)) {
            return null;
        }

        $content = file_get_contents($this->envPath);

        if (preg_match('/^' . preg_quote($key, '/') . '=(.*)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    public function remove(string $key): bool
    {
        if (!file_exists($this->envPath)) {
            return false;
        }

        $content = file_get_contents($this->envPath);

        $content = preg_replace('/^' . preg_quote($key, '/') . '=.*$\n?/m', '', $content);

        return file_put_contents($this->envPath, $content) !== false;
    }

    public function clearConfigCache(): bool
    {
        $commands = [
            'php artisan config:clear',
            'php artisan cache:clear',
        ];

        foreach ($commands as $command) {
            exec($command . ' 2>&1', $output, $returnCode);
            if ($returnCode !== 0) {
                return false;
            }
        }

        return true;
    }
}