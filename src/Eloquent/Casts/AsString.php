<?php

namespace Pelfox\LaravelBigQuery\Eloquent\Casts;

use Illuminate\Database\Eloquent\Model;
use Pelfox\LaravelBigQuery\Types\StringType;

class AsString extends Cast
{

    public function getValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return (string)$value;
    }

    public function setValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return new StringType($value);
    }
}
