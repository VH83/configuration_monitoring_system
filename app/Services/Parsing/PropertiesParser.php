<?php

namespace App\Services\Parsing;

use App\Exceptions\InvalidConfigurationException;

class PropertiesParser implements ConfigurationParser
{
    public function parse(string $content): array
    {
        $result = [];

        foreach (preg_split('/\r\n|\r|\n/', $content) as $lineNumber => $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, '!')) {
                continue;
            }

            if (! preg_match('/^([^:=\s]+)\s*[:=]\s*(.*)$/', $line, $matches)) {
                throw new InvalidConfigurationException('Invalid properties syntax on line '.($lineNumber + 1));
            }

            $result[$matches[1]] = $this->cast(trim($matches[2]));
        }

        return $result;
    }

    private function cast(string $value): mixed
    {
        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => is_numeric($value) ? (str_contains($value, '.') ? (float) $value : (int) $value) : $value,
        };
    }
}