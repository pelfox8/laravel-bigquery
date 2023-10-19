<?php

namespace Pelfox\LaravelBigQuery\Types;

class StringType extends BaseType
{
    public function __construct($value)
    {
        parent::__construct((string)$value);
    }

    public function formattedQueryValue()
    {
        return $this->escapeValue();
    }
}
