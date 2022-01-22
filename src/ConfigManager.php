<?php

declare(strict_types=1);

namespace Medas\ConfigManager;

use Dotenv\Dotenv;
use Medas\FileSystem\DirectoryManager;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\ConfigOptions\ConfigOption;
use Medas\ServiceManager\ConfigOptions\OptionController;
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

    public function __construct(
        private DirectoryManager $directoryManager,
        private EnvValueInserter $envValueInserter,
        private OptionController $optionController,
    )
    {
        $this->values = new DataTree();
    }

    public function readEnv(string $filePath): self
    {
        $dotEnv = Dotenv::createImmutable($filePath);
        $dotEnv->load();

        return $this;
    }

    public function addDirectory(string $directory): self
    {
        if (!file_exists($directory)) {
            throw new  \Exception('directory ' . $directory . ' not found');
        }

        $this->directories[] = $directory;
        $this->loadConfigFiles($directory);

        return $this;
    }

    private function loadConfigFiles(string $directory)
    {
        $files = $this->directoryManager->recursiveFindByExtension($directory, 'yaml');

        foreach ($files as $file) {
            $this->files[] = $file;
            $this->values->mergeArray(Yaml::parseFile($file), $file);
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

    public function getOptionValue(ConfigOption $option): mixed
    {
        return $this->getValue($this->optionController->getPath($option));
    }

    public function getValue(string $path): mixed
    {
        $value = $this->values->get($path);
        return is_string($value) ? $this->envValueInserter->insert($value) : $value;
    }

    public function hasValue(string $path): bool
    {
        return $this->values->has($path);
    }
}
