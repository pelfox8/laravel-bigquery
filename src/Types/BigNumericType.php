<?php

namespace Pelfox\LaravelBigQuery\Types;

class BigNumericType extends StringType
{
    public function formattedQueryValue()
    {
        return 'bignumeric' . $this->escapeValue();
    }
}
