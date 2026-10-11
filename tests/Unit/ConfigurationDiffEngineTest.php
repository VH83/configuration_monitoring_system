<?php

namespace Tests\Unit;

use App\Services\Diff\ConfigurationDiffEngine;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConfigurationDiffEngineTest extends TestCase
{
    #[Test]
    public function it_detects_nested_added_removed_updated_and_type_changes(): void
    {
        $diff = (new ConfigurationDiffEngine)->compare(
            ['database' => ['host' => 'localhost'], 'cache' => ['enabled' => true], 'featureFlags' => 'off'],
            ['database' => ['host' => 'prod-db'], 'featureFlags' => ['newDashboard' => true], 'new' => 1],
        );

        $this->assertSame(['database.host' => ['old' => 'localhost', 'new' => 'prod-db']], $diff['updated']);
        $this->assertSame(['new' => 1], $diff['added']);
        $this->assertSame(['cache.enabled' => true], $diff['removed']);
        $this->assertSame('string', $diff['type_changes']['featureFlags']['oldType']);
    }

    #[Test]
    public function it_returns_empty_sections_when_there_are_no_changes(): void
    {
        $diff = (new ConfigurationDiffEngine)->compare(['a' => 1], ['a' => 1]);
        $this->assertSame(['added' => [], 'removed' => [], 'updated' => [], 'type_changes' => []], $diff);
    }
}