<?php

declare(strict_types=1);

namespace Medas\ConfigManagerTest;

use Medas\Core\Interfaces\Cache;

/**
 * Remembers values in memory and, like the real caches, stores what the getter returns on a miss
 */
class MockCache implements Cache
{
    private array $values = [];

    public function get(string|array $key, callable $getter, int $ttl = 0): mixed
    {
        $key = $this->key($key);

        if (!array_key_exists($key, $this->values)) {
            $this->values[$key] = $getter();
        }

        return $this->values[$key];
    }

    public function set(string|array $key, mixed $value, int $ttl = 0): void
    {
        $this->values[$this->key($key)] = $value;
    }

    public function remove(string|array $key): void
    {
        unset($this->values[$this->key($key)]);
    }

    public function contains(string|array $key): bool
    {
        return array_key_exists($this->key($key), $this->values);
    }

    private function key(string|array $key): string
    {
        return is_array($key) ? implode('.', $key) : $key;
    }
}
