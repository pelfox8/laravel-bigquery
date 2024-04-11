<?php

namespace Pelfox\LaravelBigQuery\Types;

use Pelfox\LaravelBigQuery\Escape;

class StringType extends BaseType
{
    public function __construct($value)
    {
        parent::__construct((string)$value);
    }

    public function formattedQueryValue()
    {
        return Escape::string($this->value);
    }
}
