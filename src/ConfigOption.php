<?php

declare(strict_types=1);

namespace Medas\ConfigManager;

interface ConfigOption extends \Medas\ServiceManager\Interfaces\ConfigOption
{
    public static function instance(): ConfigOption;

    public function group(): ConfigGroup;

    public function name(): string;

    public function description(): string;

    public function isValid(mixed $value): bool;

    public function default(): mixed;
}
