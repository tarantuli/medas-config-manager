<?php

declare(strict_types=1);

namespace Medas\Test\MockUps;

use Medas\ConfigManager\ConfigGroup;
use Medas\ServiceManager\AsSingleton;

class MockUpConfigGroup implements ConfigGroup
{
    use AsSingleton;

    public function parent(): ConfigGroup|null
    {
        return MockUpConfigGroupRoot::instance();
    }

    public function name(): string
    {
        return 'mock-group';
    }
}
