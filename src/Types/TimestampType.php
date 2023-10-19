<?php

namespace Pelfox\LaravelBigQuery\Types;

use Illuminate\Support\Carbon;

class TimestampType extends DateTimeType
{
    protected string $format = 'Y-m-d H:i:s.uP';

    public function __construct($value)
    {
        $value = $value instanceof \DateTimeInterface
            ? $value
            : (is_numeric($value)
                ? Carbon::createFromTimestamp($value)
                : Carbon::parse($value));
        parent::__construct($value);
    }
}
