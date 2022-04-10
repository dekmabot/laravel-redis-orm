<?php declare(strict_types=1);

namespace LaravelRedisOrm;

use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis as LaravelRedis;

class Redis
{
    private string|null $connection;
    private string $keyPrefix;

    public function __construct(null|string $connection, string $keyPrefix)
    {
        $this->connection = $connection;
        $this->keyPrefix = $keyPrefix;
    }

    /**
     * Get value by key
     * @param string $realKey
     * @return array|null
     */
    public function get(string $realKey): array|null
    {
        $data = $this->connection()->command('GET', [$realKey]);
        if ($data === false) {
            return null;
        }

        return $this->unserialize($data);
    }

    /**
     * Get values by keys
     * @param array $keys
     * @return iterable
     */
    public function multiGet(array $keys): iterable
    {
        $realKeys = [];
        foreach ($keys as $key) {
            $realKeys[] = $this->getKey($key);
        }

        $found = [];

        $result = $this->connection()->command('MGET', [$realKeys]);
        foreach ($keys as $i => $key) {
            $data = $result[$i];
            if ($data === false) {
                continue;
            }

            $found[$key] = $this->unserialize($data);
        }

        return $found;

    }

    /**
     * Get key-list values
     * @param string $realKey
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function lRange(string $realKey, int $offset = 0, int $limit = -1): array
    {
        $result = $this->connection()->command('LRANGE', [$realKey, $offset, $limit]);
        if (empty($result)) {
            return [];
        }

        return $result;
    }

    /**
     * Add one key-list value
     * @param string $realKey
     * @param int|string $key
     * @return bool
     */
    public function rPush(string $realKey, int|string $key): bool
    {
        return (bool)$this->connection()->command('RPUSH', [$realKey, $key]);
    }

    /**
     * Delete one key-list value
     * @param string $realKey
     * @param int|string $key
     * @return bool
     */
    public function lRem(string $realKey, int|string $key): bool
    {
        return (bool)$this->connection()->command('LREM', [$realKey, $key, 1]);
    }

    /**
     * Set value by key
     * @param Model $model
     * @return bool
     */
    public function set(Model $model): bool
    {
        $realKey = $this->getKey($model->getKey());
        $value = $this->serialize($model);

        return (bool)$this->connection()->command('SET', [$realKey, $value]);
    }

    /**
     * Delete key value
     * @param Model $model
     * @return bool
     */
    public function del(Model $model): bool
    {
        $realKey = $this->getKey($model->getKey());

        return (bool)$this->connection()->command('DEL', [$realKey]);
    }

    public function getKey(int|string $key, string|null $keyPrefix = null): string
    {
        if ($keyPrefix === null) {
            $keyPrefix = $this->keyPrefix;
        }

        return $keyPrefix . '::' . $key;
    }

    private function connection(): Connection
    {
        return LaravelRedis::connection($this->connection);
    }

    private function serialize(Model $model): string
    {
        $values = $model->toArray();
        unset($values[$model->primaryKey]);
        ksort($values);

        return serialize($values);
    }

    private function unserialize(string $data): array
    {
        return unserialize($data, [false]);
    }
}
