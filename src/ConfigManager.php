<?php

declare(strict_types=1);

namespace Medas\ConfigManager;

use Dotenv\Dotenv;
use Medas\Core\Directory;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\DataTree;
use Symfony\Component\Yaml\Yaml;

#[Service]
class ConfigManager implements \Medas\ServiceManager\Interfaces\ConfigManager
{
    /** @var string[] $directories */
    private array $directories = [];
    /** @var string[] $files */
    private array $files = [];
    private DataTree $values;

    public function __construct()
    {
        $this->values = new DataTree();
    }

    public function readEnv(string $filePath): void
    {
        $dotEnv = Dotenv::createImmutable($filePath);
        $dotEnv->load();
    }

    public function addDirectory(string $directory): void
    {
        $this->directories[] = $directory;
        $this->loadConfigFiles($directory);
    }

    private function loadConfigFiles(string $directory)
    {
        $files = Directory::recursiveFindByExtension($directory, 'yaml');

        foreach ($files as $file) {
            $this->files[] = $file;
            $this->values->setRecursively(Yaml::parseFile($file), $file);
        }
    }

    public function getDirectories(): array
    {
        return $this->directories;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function getValue(string $path): mixed
    {
        $value = $this->values->get($path);
        return is_string($value) ? $this->insertEnvValues($value) : $value;
    }

    private function insertEnvValues(string $value): string
    {
        if (preg_match_all('/\$env\((\w+)\)/', $value, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $value = str_replace($match[0], $_ENV[$match[1]], $value);
            }
        }

        return $value;
    }

    public function hasValue(string $path): bool
    {
        return $this->values->has($path);
    }
}
