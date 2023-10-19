<?php

namespace Pelfox\LaravelBigQuery\Types;

class BooleanType extends BaseType
{
    public function __construct($value)
    {
        parent::__construct((bool)$value);
    }

    public function formattedQueryValue()
    {
        return $this->value ? 'true' : 'false';
    }
}
