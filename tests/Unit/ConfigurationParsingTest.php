<?php

namespace Tests\Unit;

use App\Exceptions\InvalidConfigurationException;
use App\Services\Parsing\EnvParser;
use App\Services\Parsing\JsonParser;
use App\Services\Parsing\PropertiesParser;
use App\Services\Parsing\YamlParser;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConfigurationParsingTest extends TestCase
{
    #[Test]
    public function it_parses_env_values(): void
    {
        $this->assertSame(['APP_ENV' => 'production', 'APP_DEBUG' => false, 'PORT' => 3306], (new EnvParser)->parse("APP_ENV=production\nAPP_DEBUG=false\nPORT=3306"));
    }

    #[Test]
    public function it_rejects_invalid_env(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        (new EnvParser)->parse('not valid');
    }

    #[Test]
    public function it_parses_json_yaml_and_properties(): void
    {
        $this->assertSame(['enabled' => true], (new JsonParser)->parse('{"enabled":true}'));
        $this->assertSame(['feature' => ['enabled' => false]], (new YamlParser)->parse("feature:\n  enabled: false"));
        $this->assertSame(['name' => 'demo', 'port' => 5], (new PropertiesParser)->parse("name=demo\nport=5"));
    }
}