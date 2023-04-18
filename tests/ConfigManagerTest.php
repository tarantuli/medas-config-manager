<?php

declare(strict_types=1);

namespace Medas\Test;

use Medas\ConfigManager\ConfigManager;
use PHPUnit\Framework\TestCase;

class ConfigManagerTest extends TestCase
{
    public function testReadTypes(): void
    {
        $config = $this->getConfig();

        $this->assertEquals('string', $config->getValue('path.to.string-variable'));
        $this->assertEquals('quoted', $config->getValue('path.to.quoted-variable'));

        $this->assertEquals('int', get_debug_type($config->getValue('path.to.int-variable')));
    }

    private function getConfig(): ConfigManager
    {
        return service(ConfigManager::class);
    }

    public function testInsertEnvValues(): void
    {
        $config = $this->getConfig();
        self::assertEquals($_ENV['DB_DNS'], $config->getValue('db.dns'));
    }
}
