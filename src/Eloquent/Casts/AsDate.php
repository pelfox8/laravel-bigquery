<?php

namespace Pelfox\LaravelBigQuery\Eloquent\Casts;

use Illuminate\Database\Eloquent\Model;
use Pelfox\LaravelBigQuery\Types\DateType;

class AsDate extends AsDateTime
{
    public function setValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return new DateType($value);
    }
}
