<?php

declare(strict_types=1);

namespace Medas\ConfigManagerTest;

use Medas\ConfigManager\ConfigManager;
use PHPUnit\Framework\TestCase;

class ConfigManagerTest extends TestCase
{
    public function testReadTypes(): void
    {
        $config = $this->getConfig();

        $this->assertEquals('string', $config->getValue('path.to.string-variable'));
        $this->assertEquals('quoted', $config->getValue('path.to.quoted-variable'));
        $this->assertEquals(ExampleEnum::Value1, $config->getValue('path.to.enum-variable'));
        $this->assertEquals(PHP_INT_MAX, $config->getValue('path.to.max-int-variable'));
    }

    public function testInsertEnvValues(): void
    {
        $config = $this->getConfig();

        self::assertEquals('127.0.0.1', $config->getValue('db.dsn'));
    }

    private function getConfig(): ConfigManager
    {
        return service(ConfigManager::class);
    }
}
