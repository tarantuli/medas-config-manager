<?php

declare(strict_types=1);

namespace Medas\Test;

use Medas\Test\MockUps\MockConfigOption;
use PHPUnit\Framework\TestCase;

class ConfigOptionTest extends TestCase
{
    public function testOption(): void
    {
        $option = new MockConfigOption();

        self::assertTrue($option->isValid(false));
        self::assertNotTrue($option->isValid('string'));
    }
}
