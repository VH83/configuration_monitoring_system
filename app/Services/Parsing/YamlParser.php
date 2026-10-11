<?php

namespace App\Services\Parsing;

use App\Exceptions\InvalidConfigurationException;

class YamlParser implements ConfigurationParser
{
    public function parse(string $content): array
    {
        if (trim($content) === '') {
            return [];
        }

        $result = [];
        $stack = [ -1 => &$result ];
        foreach (preg_split('/\r\n|\r|\n/', $content) as $lineNumber => $line) {
            if (trim($line) === '' || str_starts_with(trim($line), '#')) {
                continue;
            }

            $indent = strlen($line) - strlen(ltrim($line, ' '));
            if ($indent % 2 !== 0 || ! preg_match('/^\s*([A-Za-z0-9_.-]+):(?:\s+(.*))?$/', $line, $matches)) {
                throw new InvalidConfigurationException('Unsupported or invalid YAML on line '.($lineNumber + 1));
            }

            while (array_key_last($stack) >= $indent) {
                array_pop($stack);
            }

            $parent = &$stack[array_key_last($stack)];
            $value = $matches[2] ?? '';
            $parent[$matches[1]] = $value === '' ? [] : $this->cast(trim($value));
            if ($value === '') {
                $stack[$indent] = &$parent[$matches[1]];
            }
        }

        return $result;
    }

    private function cast(string $value): mixed
    {
        $value = trim($value, " \t\"'");

        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            'null', '~' => null,
            default => is_numeric($value) ? (str_contains($value, '.') ? (float) $value : (int) $value) : $value,
        };
    }
}