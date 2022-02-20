<?php declare(strict_types=1);

namespace LaravelRedisOrm\Relations;

use LaravelRedisOrm\Model;
use LaravelRedisOrm\Redis;

class BelongsTo
{
    protected Model $belongsToModel;
    protected string|Model $hasManyClass;
    protected string $foreignKey;

    protected null|string $connection;
    protected string $keyPrefix;

    public function __construct(string|Model $hasManyClass, Model $belongsToModel, string $foreignKey, null|string $connection)
    {
        $this->belongsToModel = $belongsToModel;
        $this->foreignKey = $foreignKey;
        $this->connection = $connection;

        $this->keyPrefix = (new $hasManyClass)->table . '_' . $belongsToModel->table;
    }

    public function get(): Model|null
    {
        $key = $this->belongsToModel->getAttributeValue($this->foreignKey);

        return $this->hasManyClass::query()->findOne($key);
    }

    public function attachTo(Model $hasManyModel): bool
    {
        $realKey = $this->connection()->getKey($hasManyModel->getKey(), $this->keyPrefix);

        return $this->connection()->rPush($realKey, $this->belongsToModel->getKey());
    }

    public function detachFrom(Model $hasManyModel): bool
    {
        $realKey = $this->connection()->getKey($hasManyModel->getKey(), $this->keyPrefix);

        return $this->connection()->lRem($realKey, $this->belongsToModel->getKey());
    }

    private function connection(): Redis
    {
        return new Redis($this->connection, $this->keyPrefix);
    }
}
