<?php

declare(strict_types=1);

namespace Medas\Test;

use Medas\ConfigManager\ConfigManager;
use Medas\Test\MockUps\MockConfigOption;
use PHPUnit\Framework\TestCase;

class ConfigOptionTest extends TestCase
{
    public function testOptionPath(): void
    {
        $option = MockConfigOption::instance();
        $manager = service(ConfigManager::class);

        $value = $manager->getOptionValue($option);

        self::assertEquals('mock-value', $value);
    }

    public function testOptionValidator(): void
    {
        $option = MockConfigOption::instance();

        self::assertTrue($option->isValid(false));
        self::assertNotTrue($option->isValid('string'));
    }
}
