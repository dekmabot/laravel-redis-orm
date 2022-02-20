<?php declare(strict_types=1);

namespace LaravelRedisOrm;

class Query
{
    protected string|Model $className;

    public function __construct(string|Model $className)
    {
        $this->className = $className;
    }

    public function findOne(int|string $key): Model|null
    {
        $realKey = $this->className::storage()->getKey($key);
        $row = $this->className::storage()->get($realKey);

        /** @var Model $model */
        $model = new $this->className();
        $model->hydrate($row);
        $model->setKey($key);

        return $model;
    }

    /**
     * @param array $keys
     * @return Model[]
     */
    public function findMany(array $keys): iterable
    {
        $rows = $this->className::storage()->multiGet($keys);

        $result = [];
        foreach($rows as $key => $row){
            $model = new $this->className();
            $model->hydrate($row);
            $model->setKey($key);

            $result[] = $model;
        }

        return $result;
    }
}
