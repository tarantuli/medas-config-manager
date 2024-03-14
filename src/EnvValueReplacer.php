<?php

declare(strict_types=1);

namespace Medas\ConfigManager;

use Medas\Core\Attributes\Service;

#[Service]
readonly class EnvValueReplacer
{
    public function process(string $value, array $env): string|null
    {
        if (preg_match('/^\$env\((\w+)\)$/', $value, $match)) {
            // If the string as a whole refers to one ENV variable, and that one isn't set,
            // return null
            return array_key_exists($match[1], $env) ? $env[$match[1]] : null;
        }

        if (preg_match('/^\$envJson\((\w+)\)$/', $value, $match)) {
            // If the string as a whole refers to one ENV variable, and that one isn't set,
            // return null
            return array_key_exists($match[1], $env) ? json_decode($env[$match[1]]) : null;
        }

        if (preg_match_all('/\$env\((\w+)\)/', $value, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (!array_key_exists($match[1], $env)) {
                    throw new Exceptions\EnvVariableNotFound($match[1]);
                }

                $value = str_replace($match[0], $env[$match[1]], $value);
            }
        }

        return $value;
    }
}
