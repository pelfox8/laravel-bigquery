<?php

namespace Pelfox\LaravelBigQuery\Types;

class JsonType extends BaseType
{
    public function formattedQueryValue(): string
    {
        return "json'" .json_encode($this->value) . "'";
    }

    public function __toString(): string
    {
        return '(Array)';
    }
}
