<?php declare(strict_types=1);

namespace LaravelRedisOrm;

use LaravelRedisOrm\Traits\Attributes;
use LaravelRedisOrm\Traits\Relations;

abstract class Model
{
    use Attributes, Relations;

    public string $table = '';
    public static null|string $connection = null;

    public static function query(): Query
    {
        return new Query(static::class);
    }

    public static function storage(): Redis
    {
        return new Redis(static::$connection, (new static)->table);
    }

    public function save(): bool
    {
        return self::storage()->set($this);
    }

    public function delete(): bool
    {
        return self::storage()->del($this);
    }
}
