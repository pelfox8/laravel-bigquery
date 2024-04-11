<?php

namespace Pelfox\LaravelBigQuery\Types;

use Pelfox\LaravelBigQuery\Escape;

class RepeatedMode extends BaseType
{

    public function __construct($value)
    {
        parent::__construct((array)$value);
    }

    /**
     * @throws \Exception
     */
    public function formattedQueryValue()
    {
        $values = [];
        foreach ($this->value as $item){
            $values[] = Escape::any($item);
        }
        $join = implode(', ', $values);
        return "[$join]";
    }

    public function __toString(): string
    {
        return '(Array)';
    }
}
