<?php

namespace Pelfox\LaravelBigQuery\Eloquent\Casts;

use Illuminate\Database\Eloquent\Model;
use Pelfox\LaravelBigQuery\Types\JsonType;

class AsJson extends Cast
{

    public function getValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return $value;
    }

    public function setValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return new JsonType($value);
    }
}
