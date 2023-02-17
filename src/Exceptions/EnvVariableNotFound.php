<?php

declare(strict_types=1);

namespace Medas\ConfigManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class EnvVariableNotFound extends BaseException
{
    public function __construct(string $variableName)
    {
        parent::__construct($variableName);
    }

    public function pattern(): string
    {
        return 'env variable "%s" not found';
    }
}
