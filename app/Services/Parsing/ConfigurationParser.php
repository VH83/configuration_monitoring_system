<?php

namespace App\Services\Parsing;

interface ConfigurationParser
{
    /** @return array<string, mixed> */
    public function parse(string $content): array;
}