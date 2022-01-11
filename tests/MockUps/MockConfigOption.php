<?php

declare(strict_types=1);

namespace Medas\Test\MockUps;

use Medas\ConfigManager\ConfigOption;

class MockConfigOption implements ConfigOption
{

    public function path(): string
    {
        return 'config.mockups.use-test-variables';
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
