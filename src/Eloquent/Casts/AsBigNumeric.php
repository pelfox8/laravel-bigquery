<?php

namespace Pelfox\LaravelBigQuery\Eloquent\Casts;

use Illuminate\Database\Eloquent\Model;
use Pelfox\LaravelBigQuery\Types\BigNumericType;

class AsBigNumeric extends AsString
{
    public function setValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return new BigNumericType($value);
    }
}
