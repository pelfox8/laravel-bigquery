<?php

namespace Pelfox\LaravelBigQuery\Eloquent\Casts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Pelfox\LaravelBigQuery\Types\DateTimeType;

class AsDateTime extends Cast
{

    public function getValue(Model $model, string $key, mixed $value, array $attributes)
    {
        if ($value instanceof \DateTimeInterface){
            return $value;
        }
        return is_numeric($value)
            ? Carbon::createFromTimestamp($value)
            : Carbon::parse($value);
    }

    public function setValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return new DateTimeType($value);
    }
}
