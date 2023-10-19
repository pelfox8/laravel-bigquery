<?php

namespace Pelfox\LaravelBigQuery\Types;

class FloatType extends BaseType
{
    public function __construct($value)
    {
        parent::__construct((float)$value);
    }
}
