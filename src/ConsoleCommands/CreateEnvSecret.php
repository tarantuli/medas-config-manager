<?php

declare(strict_types=1);

namespace Medas\ConfigManager\ConsoleCommands;

use Medas\ConfigManager\Exceptions\{InvalidSecretName, SecretAlreadyExistsInEnvFile};
use Medas\Console\Commands\{
    Argument,
    BaseConsoleCommand,
    CommandInput,
    ConsoleCommandGroup,
    Option
};
use Medas\Core\{
    Attributes\Service,
    CodeGenerator,
    Exceptions\FailedToReadContent,
    Exceptions\FailedToWriteContent
};

#[Service]
readonly class CreateEnvSecret extends BaseConsoleCommand
{
    public function __construct(
        private CodeGenerator      $codeGenerator,
        private ConfigManagerGroup $group,
    )
    {
    }

    public function group(): ConsoleCommandGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'create-env-secret';
    }

    public function description(): string
    {
        return 'Creates and writes a new secret to the .env file';
    }

    public function options(): array
    {
        return [
            Option::valueRequired('file', 'f'),
            Option::valueRequired('length', 'l'),
        ];
    }

    public function arguments(): array
    {
        return [
            Argument::required('name', description: 'The name of the secret to create'),
        ];
    }

    public function process(CommandInput $input): void
    {
        $file = $input->getOption('file') ?? '.env';
        $length = (int) ($input->getOption('length') ?? 32);
        $name = $input->getArgument('name');

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
            throw new InvalidSecretName($name);
        }

        if (!file_exists($file) && !touch($file)) {
            throw new FailedToWriteContent($file, 'unknown error');
        }

        $content = file_get_contents($file);

        if ($content === false) {
            throw new FailedToReadContent($file, 'unknown error');
        }

        if (preg_match('/^' . preg_quote($name, '/') . '=(.*)$/m', $content)) {
            throw new SecretAlreadyExistsInEnvFile($name, $file);
        }

        $content = rtrim($content)
            . "\n$name="
            . $this->codeGenerator->generate($length, CodeGenerator\CharacterSet::AlphaNumeric)
            . "\n";

        $writtenBytes = file_put_contents($file, $content);

        if ($writtenBytes === false) {
            throw new FailedToWriteContent($file, 'unknown error');
        }
    }
}
