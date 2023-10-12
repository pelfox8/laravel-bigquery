<?php

namespace Pelfox\LaravelBigQuery\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar as BaseGrammar;

class Grammar extends BaseGrammar
{
    protected $operators = [
        '+', '-', '~', '*', '/', '||', '<<', '>>', '&', '^', '|', '=', '<', '>',
        '<=', '>=', '!=', '<>', 'like', 'not like', 'not between', 'between',
        'not in', 'in', 'is not null', 'is null', 'is not true', 'is true',
        'is not false', 'is false', 'not', 'and', 'or'
    ];

    protected $bitwiseOperators = [
        '~', '&', '|', '<<', '>>', '<<=', '>>=', '^'
    ];

    protected string $tableSuffix = '';

    public function setSuffixTable($suffix): static
    {
        $this->tableSuffix = $suffix;
        return $this;
    }

    protected function wrapValue($value): string
    {
        return $value === '*' ? $value : '`' . $value . '`';
    }

    public function wrapTable($table)
    {
        if ($this->isExpression($table)) {
            return $this->getValue($table);
        }

        $dataset = '';
        if (str_contains($table, '.')) {
            $parts = explode('.', $table);
            $table = array_pop($parts);
            $dataset = implode('.', $parts) . '.';
        }

        return $this->wrap("{$dataset}{$this->tablePrefix}{$table}{$this->tableSuffix}", true);
    }

    public function compileDelete(Builder $query)
    {
        $this->setDefaultWheres($query);
        return parent::compileDelete($query);
    }

    protected function setDefaultWheres(Builder $query)
    {
        if (!$query->wheres) {
            $query->whereRaw('1 = 1');
        }
    }

    public function compileUpdate(Builder $query, array $values)
    {
        $this->setDefaultWheres($query);
        return parent::compileUpdate($query, $values);
    }
}
