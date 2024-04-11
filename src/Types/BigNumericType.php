<?php

namespace Pelfox\LaravelBigQuery\Types;

use Pelfox\LaravelBigQuery\Escape;

class BigNumericType extends StringType
{
    public function formattedQueryValue()
    {
        return 'bignumeric' . Escape::string($this->value);
    }
}
