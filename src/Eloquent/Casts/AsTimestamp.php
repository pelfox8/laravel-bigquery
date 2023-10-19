<?php

namespace Pelfox\LaravelBigQuery\Eloquent\Casts;

use Illuminate\Database\Eloquent\Model;
use Pelfox\LaravelBigQuery\Types\TimestampType;

class AsTimestamp extends AsDateTime
{
    public function setValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return new TimestampType($value);
    }
}
