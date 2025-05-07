<?php

namespace Pelfox\LaravelBigQuery\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar as BaseGrammar;

class Grammar extends BaseGrammar
{
    private string $deleteValue = '_delete_binding_item_';

    protected $operators = [
        '+', '-', '~', '*', '/', '||', '<<', '>>', '&', '^', '|', '=', '<', '>',
        '<=', '>=', '!=', '<>', 'like', 'not like', 'not between', 'between',
        'not in', 'in', 'is not null', 'is null', 'is not true', 'is true',
        'is not false', 'is false', 'not', 'and', 'or'
    ];

    protected $bitwiseOperators = [
        '~', '&', '|', '<<', '>>', '<<=', '>>=', '^'
    ];

    protected function wrapValue($value): string
    {
        return $value === '*' ? $value : '`' . $value . '`';
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

    protected function wrapJsonSelector($value)
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($value);

        return "json_value($field $path)";
    }

    protected function whereJsonContains(Builder $query, $where)
    {
        $not = $where['not'] ? 'not ' : '';

        $this->removeWhereBinging($query);

        return $not . $this->compileJsonContains(
                $where['column'],
                $where['value']
            );
    }

    protected function compileJsonContains($column, $value)
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($column);

        $items = [];
        foreach ((array)$value as $item) {
            $item = is_null($item) ? 'null' : '"' . str_replace('"', '\"', $item) . '"';
            $items[] = "$item in unnest(json_value_array($field$path))";
        }

        return "(" . implode(' or ', $items) . ")";
    }

    protected function removeWhereBinging(Builder $query): void
    {
        foreach ($query->bindings['where'] as $index => $binding) {
            if ($binding === $this->deleteValue) {
                unset($query->bindings['where'][$index]);
            }
        }
    }

    protected function compileJsonLength($column, $operator, $value): string
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($column);

        return "array_length(json_value_array($field$path)) $operator $value ";
    }

    public function prepareBindingForJsonContains($binding): string
    {
        return $this->deleteValue;
    }

    protected function compileJsonContainsKey($column): string
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($column);

        return "json_extract_scalar($field$path) is not null";
    }

    protected function convertBindingDateValue($type, Builder $query, $where): void
    {
        if (in_array($type, ['date', 'time'])){
            return;
        }
        foreach ($query->bindings['where'] as $index => $binding){
            if ($binding === $where['value']){
                $query->bindings['where'][$index] = (int)$binding;
            }
        }
    }

    protected function dateBasedWhere($type, Builder $query, $where): string
    {
        $value = $this->parameter($where['value']);
        $this->convertBindingDateValue($type, $query, $where);

        return 'extract(' . $type. ' from ' .$this->wrap($where['column']).') '.$where['operator'].' '.$value;
    }
}
