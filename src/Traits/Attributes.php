<?php declare(strict_types=1);

namespace LaravelRedisOrm\Traits;

trait Attributes
{
    public string $primaryKey = 'id';

    protected array $attribute = [];
    protected array $casts = [];
    protected array $fillable = [];

    private array $values = [];

    public function __get(string $attribute): mixed
    {
        return $this->getAttributeValue($attribute);
    }

    public function __set(string $attribute, mixed $value): void
    {
        $this->values[$attribute] = $value;
    }

    public function __isset(string $attribute): bool
    {
        return isset($this->values[$attribute]);
    }

    public function toArray(): array
    {
        return $this->values;
    }

    public function getKey(): int|string|null
    {
        return $this->values[$this->primaryKey];
    }

    public function getAttributeValue(string $attribute): mixed
    {
        return $this->values[$attribute] ?? null;
    }

    public function setKey(int|string $value): void
    {
        $this->values[$this->primaryKey] = $value;
    }

    public function hydrate(array $data): void
    {
        $this->values = $data;
    }
}
