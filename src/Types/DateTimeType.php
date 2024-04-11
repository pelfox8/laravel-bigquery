<?php

namespace Pelfox\LaravelBigQuery\Types;

use Illuminate\Support\Carbon;
use Pelfox\LaravelBigQuery\Escape;

class DateTimeType extends BaseType
{

    protected string $format = 'Y-m-d H:i:s';

    public function __construct($value)
    {
        $value = $value instanceof \DateTimeInterface
            ? $value
            : Carbon::parse($value);
        parent::__construct($value);
    }

    public function formattedQueryValue(): string
    {
        return Escape::string($this->__toString());
    }

    public function __toString(): string
    {
        return $this->value->format($this->format);
    }

    public function jsonSerialize(): string
    {
        return $this->__toString();
    }
}
