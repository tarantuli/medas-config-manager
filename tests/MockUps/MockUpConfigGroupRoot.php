<?php

declare(strict_types=1);

namespace Medas\Test\MockUps;

use Medas\ConfigManager\ConfigGroup;
use Medas\ServiceManager\AsSingleton;

class MockUpConfigGroupRoot implements ConfigGroup
{
    use AsSingleton;

    public function parent(): self|null
    {
        return null;
    }

    public function name(): string
    {
        return 'mock-root';
    }
}
