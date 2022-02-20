<?php declare(strict_types=1);

namespace LaravelRedisOrm\Traits;

use LaravelRedisOrm\Model;
use LaravelRedisOrm\Relations\BelongsTo;
use LaravelRedisOrm\Relations\HasMany;

/**
 * @mixin Model
 */
trait Relations
{
    protected function belongsTo(string $relatedClass, string $foreignKey): BelongsTo
    {
        return new BelongsTo($relatedClass, $this, $foreignKey, static::$connection);
    }

    protected function hasMany(string $relatedClass): HasMany
    {
        return new HasMany($relatedClass, $this, static::$connection);
    }
}
