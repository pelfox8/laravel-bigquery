<?php

namespace Pelfox\LaravelBigQuery\Types;

class BytesType extends StringType
{
    public function formattedQueryValue(): string
    {
        return 'b' . $this->escapeValue();
    }

}
