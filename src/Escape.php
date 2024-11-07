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

    public static function string($value): string
    {
        $search = ["\\", "\n", "\r", "'", '"'];
        $replace = ["\\\\", "\\n", "\\r", "\'", '\"'];

        return '"'.str_replace($search, $replace, $value).'"';
    }

    public static function json($value): string
    {
        $search = ["\\", "\n", "\r", "'", "\""];
        $replace = ["\\\\", "\\n", "\\r", "\'", '\\"'];

        return "json'".str_replace($search, $replace, json_encode($value))."'";
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
