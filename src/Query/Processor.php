<?php

namespace Pelfox\LaravelBigQuery\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor as BaseProcessor;

class Processor extends BaseProcessor
{
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null): string|int
    {
        throw new \Exception("BigQuery not support autoincrement fields");
    }
}
