<?php

namespace App\Services\Parsing;

use App\Exceptions\InvalidConfigurationException;

class ParserResolver
{
    public function __construct(
        private readonly EnvParser $env,
        private readonly JsonParser $json,
        private readonly YamlParser $yaml,
        private readonly PropertiesParser $properties,
    ) {}

    public function forPath(string $path): ConfigurationParser
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $name = strtolower(basename($path));

        return match (true) {
            $name === '.env' || str_ends_with($name, '.env') => $this->env,
            $extension === 'json' => $this->json,
            in_array($extension, ['yaml', 'yml'], true) => $this->yaml,
            $extension === 'properties' => $this->properties,
            default => throw new InvalidConfigurationException('Unsupported configuration file type.'),
        };
    }
}