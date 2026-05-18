<?php

declare(strict_types=1);

namespace Medas\ConfigManager;

use Medas\Core\Attributes\Service;

#[Service]
readonly class ValueProcessor
{
    public function __construct(
        private EnvValueReplacer $envValueReplacer,
    )
    {
    }

    public function process(mixed $value, array $env): mixed
    {
        if (is_string($value)) {
            $value = $this->envValueReplacer->process($value, $env);
        }

        if (str_contains($value, '::')) {
            [$className, $caseName] = explode('::', $value, 2);

            if (enum_exists($className)) {
                $value = constant("$className::$caseName");
            }
        }

        if (is_string($value) && defined($value)) {
            $value = constant($value);
        }

        return $value;
    }
}
