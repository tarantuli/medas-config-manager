<?php

declare(strict_types=1);

namespace Medas\ConfigManager;

use Medas\ServiceManager\Attributes\Service;

#[Service]
class OptionController
{
    public function getPath(ConfigOption $option): string
    {
        $path = $option->name();
        $group = $option->group();

        do {
            $path = $group->name() . '.' . $path;
        } while ($group = $group->parent());

        return $path;
    }
}
