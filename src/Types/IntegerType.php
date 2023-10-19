<?php

namespace Pelfox\LaravelBigQuery\Types;

class IntegerType extends BaseType
{
    public function __construct($value)
    {
        parent::__construct((int)$value);
    }
}
