<?php

namespace App\Services\Parsing;

use App\Exceptions\InvalidConfigurationException;

class JsonParser implements ConfigurationParser
{
    public function parse(string $content): array
    {
        try {
            $parsed = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new InvalidConfigurationException('Invalid JSON: '.$exception->getMessage(), previous: $exception);
        }

        if (! is_array($parsed) || array_is_list($parsed)) {
            throw new InvalidConfigurationException('JSON configuration must contain an object at the root.');
        }

        return $parsed;
    }
}