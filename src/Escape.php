<?php

namespace Pelfox\LaravelBigQuery;

use Exception;
use Pelfox\LaravelBigQuery\Types\BaseType;

class Escape
{
    /**
     * @throws Exception
     */
    public static function any($value): string
    {
        $type = gettype($value);
        return match ($type) {
            'object' => $value instanceof BaseType ? $value->formattedQueryValue() : (string)$value,
            'string' => self::string($value),
            'double',
            'integer' => $value,
            'NULL' => 'null',
            'boolean' => $value ? 'true' : 'false',
            'array' => self::array($value),
            default => throw new Exception("'$type' type not support"),
        };
    }

    public static function string($value, $wrapperSymbol = '"'): string
    {
        if (str_contains($value, PHP_EOL) || str_contains($value, $wrapperSymbol)){
            return '"""' . $value . '"""';
        }
        return $wrapperSymbol . $value . $wrapperSymbol;
    }

    /**
     * @throws Exception
     */
    public static function array($value): string
    {
        $values = [];
        foreach ($value as $v) {
            $values = self::any($v);
        }
        $join = implode(', ', $values);
        if (array_is_list($value)) {
            return '[' . $join . ']';
        }
        return '(' . $join . ')';
    }
}
