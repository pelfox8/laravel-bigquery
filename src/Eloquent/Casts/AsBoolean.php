<?php

namespace Pelfox\LaravelBigQuery\Eloquent\Casts;

use Illuminate\Database\Eloquent\Model;
use Pelfox\LaravelBigQuery\Types\BooleanType;

class AsBoolean extends Cast
{

    public function getValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return (bool)$value;
    }

    public function setValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return new BooleanType($value);
    }
}
