<?php

declare(strict_types=1);

namespace Medas\ConfigManager;

use Dotenv\Dotenv;
use Medas\Core\{Attributes\Service, Interfaces\ConfigManager as IntConfigManager};
use Medas\ServiceManager\{Cache\CacheManager, DataTree\DataTree, Mapping\FileFinder};
use Symfony\Component\Yaml\Yaml;

#[Service]
class ConfigManager implements IntConfigManager
{
    private const CACHE_KEY = 'ConfigManager::valuesAndEnv';

    private bool $valuesWereAlreadyCached = true;
    private readonly DataTree $values;
    private array $env;
    private readonly FileFinder $fileFinder;

    public function __construct(
        private readonly EnvValueReplacer $envValueReplacer,
        private readonly CacheManager     $cacheManager,
    )
    {
        [$this->values, $this->env] = $this->cacheManager->get()->get(
            self::CACHE_KEY,
            fn() => $this->initializeValues()
        );

        $this->fileFinder = new FileFinder();
    }

    private function initializeValues(): array
    {
        $this->valuesWereAlreadyCached = false;

        return [new DataTree(), $_ENV];
    }

    public function __destruct()
    {
        if (!$this->valuesWereAlreadyCached) {
            // Explicitly set the current values
            $this->cacheManager->get()->set(self::CACHE_KEY, [$this->values, $_ENV]);
        }
    }

    public function readEnv(string $filePath, string $name = null): self
    {
        if ($this->valuesWereAlreadyCached) {
            return $this;
        }

        $dotEnv = Dotenv::createImmutable($filePath, $name);

        try {
            $dotEnv->load();

            $this->env = $_ENV;
        }

        catch (\InvalidArgumentException $e) {
            throw new \Exception($e->getMessage());
        }

        return $this;
    }

    public function addDirectory(string $directory): self
    {
        if ($this->valuesWereAlreadyCached) {
            return $this;
        }

        if (!file_exists($directory)) {
            throw new \Exception('directory ' . $directory . ' not found');
        }

        $this->loadConfigFiles($directory);

        return $this;
    }

    private function loadConfigFiles(string $directory): void
    {
        $files = $this->fileFinder->recursiveFindByExtension($directory, 'yaml');

        foreach ($files as $file) {
            $this->values->mergeArray(Yaml::parseFile($file) ?? [], $file);
        }
    }

    public function hasValue(string $path): bool
    {
        return $this->values->has($path);
    }

    public function getValue(string $path): mixed
    {
        $value = $this->values->get($path);

        return is_string($value) ? $this->envValueReplacer->process($value, $this->env) : $value;
    }

    public function setValue(string $path, mixed $value): void
    {
        $this->values->set($path, $value);
    }
}
