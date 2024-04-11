<?php

namespace Pelfox\LaravelBigQuery\Types;

use Pelfox\LaravelBigQuery\Escape;

class BytesType extends StringType
{
    public function formattedQueryValue(): string
    {
        return 'b' . Escape::string($this->value);
    }

}
