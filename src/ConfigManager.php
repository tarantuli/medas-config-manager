<?php

declare(strict_types=1);

namespace Medas\ConfigManager;

use Dotenv\Dotenv;
use Medas\Core\{
    Attributes\Service,
    Interfaces\CacheManager,
    Interfaces\ConfigManager as IntConfigManager
};
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
        $this->fileFinder = $fileFinder;
        $cached = $this->cachedValuesAndEnv();

        if ($cached === null) {
            // Nothing is cached until the configuration is complete, in __destruct(): concurrent requests would
            // otherwise take a placeholder from the cache for a configuration without any values or env
            $this->valuesWereAlreadyCached = false;
            $this->values = new DataTree();
            $this->env = $_ENV;

            return;
        }

        [$this->values, $this->env] = $cached;

        // On a cache hit readEnv() is skipped, so Dotenv never repopulates the $_ENV superglobal.
        // Resolvers such as EnvValueResolver read $_ENV directly, so restore the cached env into it.
        // '+=' keeps any real environment values already present (matches Dotenv-immutable semantics).
        $_ENV += $this->env;
    }

    /**
     * @return array{DataTree, array}|null
     */
    private function cachedValuesAndEnv(): array|null
    {
        $cache = service(CacheManager::class)->get();

        if (!$cache->contains(self::CACHE_KEY)) {
            return null;
        }

        // Cache::get() stores what its getter returns, so it's only called for an entry that exists; should the
        // entry disappear in between, the null it stores counts as a miss here and is overwritten in __destruct()
        $cached = $cache->get(self::CACHE_KEY, static fn() => null);

        return is_array($cached) ? $cached : null;
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
