<?php

namespace Pelfox\LaravelBigQuery\Eloquent\Casts;

use Illuminate\Database\Eloquent\Model;
use Pelfox\LaravelBigQuery\Types\TimeType;

class AsTime extends AsDateTime
{
    public function setValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return new TimeType($value);
    }
}
