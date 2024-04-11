<?php

namespace Pelfox\LaravelBigQuery\Types;

abstract class BaseType implements \JsonSerializable, \Stringable
{

    public function __construct(public $value)
    {
    }

    public function get()
    {
        return $this->value;
    }

    public function formattedQueryValue()
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    public function jsonSerialize(): mixed
    {
        return $this->value;
    }
}
