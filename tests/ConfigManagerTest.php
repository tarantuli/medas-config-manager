<?php

declare(strict_types=1);

namespace Medas\Test;

use Medas\ConfigManager\ConfigManager;
use Medas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class ConfigManagerTest extends TestCase
{
    public function testReadTypes(): void
    {
        $config = $this->getConfig();
        $this->assertEquals('string', $config->getValue('path.to.string-variable'));
        $this->assertEquals('quoted', $config->getValue('path.to.quoted-variable'));
        $this->assertEquals(12, $config->getValue('path.to.int-variable'));
    }

    private function getConfig(): ConfigManager
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceManager::get()->resolve(ConfigManager::class);
    }

    public function testInsertEnvValues(): void
    {
        $config = $this->getConfig();
        self::assertEquals($_ENV['DB_DNS'], $config->getValue('db.dns'));
    }
}
