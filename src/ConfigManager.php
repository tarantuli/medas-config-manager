<?php

declare(strict_types=1);

namespace Medas\ConfigManager;

use Dotenv\Dotenv;
use Medas\Core\{Attributes\Service, Interfaces\ConfigManager as IntConfigManager};
use Medas\ServiceManager\{DataTree\DataTree, Mapping\FileFinder};
use Symfony\Component\Yaml\Yaml;

#[Service]
class ConfigManager implements IntConfigManager
{
    private const string CACHE_KEY = 'ConfigManager::valuesAndEnv';

    private bool $valuesWereAlreadyCached = true;
    private readonly DataTree $values;
    private array $env;
    private readonly FileFinder $fileFinder;

    public function __construct(
        private readonly ValueProcessor $valueProcessor,
        FileFinder                      $fileFinder = new FileFinder()
    )
    {
        [$this->values, $this->env] = cache(self::CACHE_KEY, fn() => $this->initializeValues());

        // On a cache hit readEnv() is skipped, so Dotenv never repopulates the $_ENV superglobal.
        // Resolvers such as EnvValueResolver read $_ENV directly, so restore the cached env into it.
        // '+=' keeps any real environment values already present (matches Dotenv-immutable semantics).
        $_ENV += $this->env;
        $this->fileFinder = $fileFinder;
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
            cacheSet(self::CACHE_KEY, [$this->values, $_ENV]);
        }
    }

    public function readEnv(string $filePath, string|null $name = null): self
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
            throw new \InvalidArgumentException($e->getMessage(), previous: $e);
        }

        return $this;
    }

    public function addDirectory(string $directory): self
    {
        if ($this->valuesWereAlreadyCached) {
            return $this;
        }

        if (!file_exists($directory)) {
            throw new \InvalidArgumentException(sprintf('Directory "%s" not found.', $directory));
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

        return $this->valueProcessor->process($value, $this->env);
    }

    public function setValue(string $path, mixed $value): void
    {
        $this->values->set($path, $value);
    }
}
