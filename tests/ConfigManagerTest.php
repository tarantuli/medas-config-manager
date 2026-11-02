<?php

declare(strict_types=1);

namespace Medas\ConfigManagerTest;

use Medas\ConfigManager\{ConfigManager, ValueProcessor};
use Medas\Core\Interfaces\CacheManager;
use PHPUnit\Framework\TestCase;

class ConfigManagerTest extends TestCase
{
    // ConfigManager::CACHE_KEY is private
    private const string CACHE_KEY = 'ConfigManager::valuesAndEnv';

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

    /**
     * A request that finds no cached configuration must not cache anything until its configuration is complete:
     * a concurrent request would otherwise take that placeholder for a configuration without any values
     */
    public function testNothingIsCachedBeforeTheConfigurationIsComplete(): void
    {
        $cacheManager = service(CacheManager::class);
        $previousCache = $cacheManager->get();
        $cache = new MockCache();

        $cacheManager->register($cache);

        try {
            $config = new ConfigManager(service(ValueProcessor::class));

            self::assertFalse($cache->contains(self::CACHE_KEY));

            $config->readEnv(__DIR__)->addDirectory(__DIR__ . '/MockConfig');

            self::assertFalse($cache->contains(self::CACHE_KEY));

            // The complete configuration is cached when the request ends
            unset($config);

            self::assertTrue($cache->contains(self::CACHE_KEY));

            // And a later request uses it without reading anything
            $cachedConfig = new ConfigManager(service(ValueProcessor::class));

            self::assertEquals('127.0.0.1', $cachedConfig->getValue('db.dsn'));
            self::assertEquals('string', $cachedConfig->getValue('path.to.string-variable'));
        }

        finally{
            $cacheManager->register($previousCache);
        }
    }
}
