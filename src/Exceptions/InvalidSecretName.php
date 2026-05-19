<?php

declare(strict_types=1);

namespace Medas\ConfigManager\Exceptions;

use Medas\Core\Exceptions\{BaseException, Suggestions};

class InvalidSecretName extends BaseException implements Suggestions
{
    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    public function pattern(): string
    {
        return 'Invalid secret name %s';
    }

    public function suggestions(): array
    {
        return ['Secret names must be alphanumeric and contain only letters, numbers, and underscores.'];
    }
}
