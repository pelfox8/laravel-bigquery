<?php

namespace Pelfox\LaravelBigQuery\Query;

use Illuminate\Database\Query\Grammars\Grammar as BaseGrammar;

class Grammar extends BaseGrammar
{
    protected string $tableSuffix = '';

    public function setSuffixTable($suffix)
    {
        $this->tableSuffix = $suffix;
        return $this;
    }

    protected function wrapValue($value)
    {
        return $value === '*' ? $value : '`' . $value . '`';
    }

    public function wrapTable($table)
    {
        if (! $this->isExpression($table)) {
            $dataset = '';
            if (str_contains($table, '.')){
                $parts = explode('.', $table);
                $table = array_pop($parts);
                $dataset = implode('.', $parts) . '.';
            }
            return $this->wrap("{$dataset}{$this->tablePrefix}{$table}{$this->tableSuffix}", true);
        }

        return $this->getValue($table);
    }
}
