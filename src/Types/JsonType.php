<?php

namespace Pelfox\LaravelBigQuery\Types;

use Pelfox\LaravelBigQuery\Escape;

class JsonType extends BaseType
{
    public function formattedQueryValue(): string
    {
        return Escape::json($this->value);
    }

    public function __toString(): string
    {
        return '(Array)';
    }
}
