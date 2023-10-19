<?php

namespace Pelfox\LaravelBigQuery\Eloquent\Casts;

use Illuminate\Database\Eloquent\Model;
use Pelfox\LaravelBigQuery\Types\IntegerType;

class AsInteger extends Cast
{

    public function getValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return (int)$value;
    }

    public function setValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return new IntegerType($value);
    }
}
