<?php

namespace App\Services\Parsing;

use App\Exceptions\InvalidConfigurationException;

class EnvParser implements ConfigurationParser
{
    public function parse(string $content): array
    {
        $result = [];

        foreach (preg_split('/\r\n|\r|\n/', $content) as $lineNumber => $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $line = preg_replace('/^export\s+/', '', $line);
            if (! preg_match('/^([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/', $line, $matches)) {
                throw new InvalidConfigurationException('Invalid .env syntax on line '.($lineNumber + 1));
            }

            $value = trim($matches[2]);
            if (($value[0] ?? null) === '"' && str_ends_with($value, '"')) {
                $value = stripcslashes(substr($value, 1, -1));
            } elseif (($value[0] ?? null) === "'" && str_ends_with($value, "'")) {
                $value = substr($value, 1, -1);
            } elseif (str_contains($value, ' #')) {
                $value = trim(strstr($value, ' #', true));
            }

            $result[$matches[1]] = $this->cast($value);
        }

        return $result;
    }

    private function cast(string $value): mixed
    {
        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => is_numeric($value) && ! str_contains($value, '.') ? (int) $value : (is_numeric($value) ? (float) $value : $value),
        };
    }
}