<?php

namespace Pelfox\LaravelBigQuery\Types;

use Pelfox\LaravelBigQuery\Escape;

class NumericType extends StringType
{
    public function formattedQueryValue()
    {
        return 'numeric' . Escape::string($this->value);
    }
}
