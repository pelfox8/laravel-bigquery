<?php

namespace Pelfox\LaravelBigQuery\Types;

class DateType extends DateTimeType
{
    protected string $format = 'Y-m-d';
}
