<?php

namespace Pelfox\LaravelBigQuery\Eloquent\Casts;

use Illuminate\Database\Eloquent\Model;
use Pelfox\LaravelBigQuery\Types\NumericType;

class AsNumeric extends AsString
{
    public function setValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return new NumericType($value);
    }
}
