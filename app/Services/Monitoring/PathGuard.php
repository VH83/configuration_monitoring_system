<?php

namespace App\Services\Monitoring;

use InvalidArgumentException;

class PathGuard
{
    public function normalize(string $path): string
    {
        $root = realpath((string) config('monitoring.root')) ?: $this->absolute(config('monitoring.root'));
        $candidate = $this->absolute($path, str_contains(str_replace('\\', '/', $path), '/') ? base_path() : $root);
        $normalizedRoot = rtrim(str_replace('\\', '/', $root), '/');
        $normalizedCandidate = str_replace('\\', '/', $candidate);

        if ($normalizedCandidate !== $normalizedRoot && ! str_starts_with($normalizedCandidate, $normalizedRoot.'/')) {
            throw new InvalidArgumentException('The path must be inside WATCH_ROOT.');
        }

        $name = basename($normalizedCandidate);
        if ($name === '' || in_array(strtolower($name), ['.git', 'id_rsa'], true) || (str_starts_with($name, '.') && $name !== '.env')) {
            throw new InvalidArgumentException('Hidden or sensitive paths are not allowed.');
        }

        $extension = strtolower(pathinfo($normalizedCandidate, PATHINFO_EXTENSION));
        if ($name !== '.env' && ! in_array($extension, ['json', 'yaml', 'yml', 'properties'], true)) {
            throw new InvalidArgumentException('Unsupported configuration file type.');
        }

        return $normalizedCandidate;
    }

    private function absolute(string $path, ?string $base = null): string
    {
        $path = str_replace('\\', '/', $path);
        if (preg_match('/^[A-Za-z]:\//', $path) || str_starts_with($path, '/')) {
            $absolute = $path;
        } else {
            $absolute = rtrim(str_replace('\\', '/', $base ?: base_path()), '/').'/'.$path;
        }

        $parts = [];
        foreach (explode('/', $absolute) as $part) {
            if ($part === '' || $part === '.') continue;
            if ($part === '..') { array_pop($parts); continue; }
            $parts[] = $part;
        }

        return (preg_match('/^[A-Za-z]:/', $absolute) ? $parts[0].'/' : '/').implode('/', array_slice($parts, preg_match('/^[A-Za-z]:/', $absolute) ? 1 : 0));
    }
}