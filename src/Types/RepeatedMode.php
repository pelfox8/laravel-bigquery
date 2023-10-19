<?php

namespace Pelfox\LaravelBigQuery\Types;

class RepeatedMode extends BaseType
{

    public function __construct($value)
    {
        parent::__construct((array)$value);
    }

    public function formattedQueryValue()
    {
        $values = [];
        foreach ($this->value as $item){
            if ($item instanceof BaseType){
                $values[] = $item->formattedQueryValue();
            }else{
                $values[] = (string)$item;
            }
        }
        $join = implode(', ', $values);
        return "[$join]";
    }

    public function __toString(): string
    {
        return '(Array)';
    }
}
