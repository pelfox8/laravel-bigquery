<?php

namespace Pelfox\LaravelBigQuery\Eloquent\Casts;

use Illuminate\Database\Eloquent\Model;
use Pelfox\LaravelBigQuery\Types\BytesType;

class AsBytes extends Cast
{

    public function getValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return $value;
    }

    public function setValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return new BytesType($value);
    }
}
