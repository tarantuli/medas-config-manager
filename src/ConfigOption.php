<?php

declare(strict_types=1);

namespace Medas\ConfigManager;

interface ConfigOption
{
    public function path(): string;

    public function description(): string;

    public function isValid(mixed $value): bool;

    public function default(): mixed;
}
