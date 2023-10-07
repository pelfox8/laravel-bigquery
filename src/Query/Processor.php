<?php

namespace Pelfox\LaravelBigQuery\Query;

use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor as BaseProcessor;

class Processor extends BaseProcessor
{
    /**
     * @throws Exception
     */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null): string|int
    {
        throw new Exception("BigQuery not support autoincrement fields");
    }
}
