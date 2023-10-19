<?php

namespace Pelfox\LaravelBigQuery\Types;

class NumericType extends StringType
{
    public function formattedQueryValue()
    {
        return 'numeric' . $this->escapeValue();
    }
}
