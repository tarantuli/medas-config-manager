<?php

declare(strict_types=1);

namespace Medas\ConfigManager;

use Medas\FileSystem\FileSystemPackage;
use Medas\ServiceManager\BasePackage;

class ConfigManagerPackage extends BasePackage
{
    public function dependencies(): array
    {
        return $this->dependenciesByClass([
            FileSystemPackage::class,
        ]);
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
