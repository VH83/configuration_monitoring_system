<?php

namespace App\Services\Diff;

class ConfigurationDiffEngine
{
    /** @return array{added: array, removed: array, updated: array, type_changes: array} */
    public function compare(array $before, array $after): array
    {
        $diff = ['added' => [], 'removed' => [], 'updated' => [], 'type_changes' => []];
        $this->compareLevel($before, $after, '', $diff);

        return $diff;
    }

    private function compareLevel(array $before, array $after, string $prefix, array &$diff): void
    {
        foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $key) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
            $hasBefore = array_key_exists($key, $before);
            $hasAfter = array_key_exists($key, $after);
            if (! $hasBefore) {
                $this->addLeaves($diff['added'], $path, $after[$key]);
            } elseif (! $hasAfter) {
                $this->addLeaves($diff['removed'], $path, $before[$key]);
            } elseif (is_array($before[$key]) && is_array($after[$key]) && ! array_is_list($before[$key]) && ! array_is_list($after[$key])) {
                $this->compareLevel($before[$key], $after[$key], $path, $diff);
            } elseif (get_debug_type($before[$key]) !== get_debug_type($after[$key])) {
                $diff['type_changes'][$path] = ['oldType' => get_debug_type($before[$key]), 'newType' => get_debug_type($after[$key]), 'old' => $before[$key], 'new' => $after[$key]];
            } elseif ($before[$key] !== $after[$key]) {
                $diff['updated'][$path] = ['old' => $before[$key], 'new' => $after[$key]];
            }
        }
    }

    private function addLeaves(array &$target, string $prefix, mixed $value): void
    {
        if (is_array($value) && ! array_is_list($value)) {
            foreach ($value as $key => $child) {
                $this->addLeaves($target, $prefix.'.'.$key, $child);
            }
        } else {
            $target[$prefix] = $value;
        }
    }
}