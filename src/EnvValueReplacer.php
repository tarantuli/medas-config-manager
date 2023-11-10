<?php

declare(strict_types=1);

namespace Medas\ConfigManager;

use Medas\Core\Attributes\Service;

#[Service]
class EnvValueReplacer
{
    private array $env;

    public function setEnv(array $env): void
    {
        $this->env = $env;
    }

    public function process(string $value): string|null
    {
        if (preg_match('/^\$env\((\w+)\)$/', $value, $match)) {
            // If the string as a whole refers to one ENV variable, and that one isn't set,
            // return null
            return array_key_exists($match[1], $this->env) ? $this->env[$match[1]] : null;
        }

        if (preg_match_all('/\$env\((\w+)\)/', $value, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (!array_key_exists($match[1], $this->env)) {
                    throw new Exceptions\EnvVariableNotFound($match[1]);
                }

                $value = str_replace($match[0], $this->env[$match[1]], $value);
            }
        }

        return $value;
    }
}
