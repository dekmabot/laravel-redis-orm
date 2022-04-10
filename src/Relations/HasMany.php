<?php declare(strict_types=1);

namespace LaravelRedisOrm\Relations;

use LaravelRedisOrm\Model;
use LaravelRedisOrm\Redis;

class HasMany
{
    protected string|Model $belongsToClass;

    protected null|string $connection;
    protected string $keyPrefix;
    protected string $realKey;

    public function __construct(string|Model $belongsToClass, Model $hasManyModel, null|string $connection = null)
    {
        $this->belongsToClass = $belongsToClass;
        $this->connection = $connection;

        $this->keyPrefix = $hasManyModel->table . '_' . (new $belongsToClass)->table;
        $this->realKey = $this->connection()->getKey($hasManyModel->getKey(), $this->keyPrefix);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function list(int $offset = 0, int $limit = -1): array
    {
        $keys = $this->connection()->lRange($this->realKey, $offset, $limit);
        if (empty($keys)) {
            return [];
        }
        $keys = array_values(array_unique($keys));

        return $this->belongsToClass::query()->findMany($keys);
    }

    public function attach(Model $belongsToModel): bool
    {
        return $this->connection()->rPush($this->realKey, $belongsToModel->getKey());
    }

    public function detach(Model $belongsToModel): bool
    {
        return $this->connection()->lRem($this->realKey, $belongsToModel->getKey());
    }

    private function connection(): Redis
    {
        return new Redis($this->connection, $this->keyPrefix);
    }
}
