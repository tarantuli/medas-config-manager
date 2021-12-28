<?php

declare(strict_types=1);

namespace Medas\ConfigManager;

use Medas\ServiceManager\Interfaces\Package;

class ConfigManagerPackage implements Package
{
    public function dependencies(): array
    {
        return [];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
