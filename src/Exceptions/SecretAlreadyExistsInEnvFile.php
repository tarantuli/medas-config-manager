<?php

declare(strict_types=1);

namespace Medas\ConfigManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class SecretAlreadyExistsInEnvFile extends BaseException
{
    public function __construct(string $name, string $file)
    {
        parent::__construct($name, $file);
    }

    public function pattern(): string
    {
        return 'A secret with name %s already exists in the %s file';
    }
}
