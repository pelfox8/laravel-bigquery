<?php

namespace Pelfox\LaravelBigQuery\Eloquent\Casts;

use Illuminate\Database\Eloquent\Model;
use Pelfox\LaravelBigQuery\Types\FloatType;

class AsFloat extends Cast
{

    public function getValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return (float)$value;
    }

    public function setValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return new FloatType($value);
    }
}
