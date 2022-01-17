<?php

declare(strict_types=1);

namespace Medas\Test\MockUps;

use Medas\ConfigManager\ConfigGroup;
use Medas\ConfigManager\ConfigOption;
use Medas\ServiceManager\AsSingleton;

class MockConfigOption implements ConfigOption
{
    use AsSingleton;

    public function group(): ConfigGroup
    {
        return MockUpConfigGroup::instance();
    }

    public function name(): string
    {
        return 'mock-option';
    }

    public function description(): string
    {
        return 'Whether we should use test variables';
    }

    public function isValid(mixed $value): bool
    {
        return is_bool($value);
    }

    public function default(): bool
    {
        return true;
    }
}
