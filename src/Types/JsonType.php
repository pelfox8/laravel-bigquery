<?php

namespace Pelfox\LaravelBigQuery\Types;

use Pelfox\LaravelBigQuery\Escape;

class JsonType extends BaseType
{
    public function formattedQueryValue(): string
    {
        $value = json_encode($this->value);

        return "json" . Escape::string($value, "'");
    }

    public function __toString(): string
    {
        return '(Array)';
    }
}
