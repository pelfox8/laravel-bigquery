<?php

namespace Pelfox\LaravelBigQuery\Types;

class TimeType extends DateTimeType
{
    protected string $format = 'H:i:s';
}
