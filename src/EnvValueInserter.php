<?php

declare(strict_types=1);

namespace Medas\ConfigManager;

use Medas\ConfigManager\Exceptions\EnvVariableNotFoundException;
use Medas\ServiceManager\Attributes\Service;

#[Service]
class EnvValueInserter
{
    public function insert(string $value): string
    {
        if (preg_match_all('/\$env\((\w+)\)/', $value, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (!array_key_exists($match[1], $_ENV)) {
                    throw new EnvVariableNotFoundException($match[1]);
                }

                $value = str_replace($match[0], $_ENV[$match[1]], $value);
            }
        }

        return $value;
    }
}
