<?php

namespace Pelfox\LaravelBigQuery;

use Closure;
use DateTimeInterface;
use Exception;
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\BigQuery\Dataset;
use Google\Cloud\BigQuery\QueryResults;
use Google\Cloud\BigQuery\ValueInterface;
use Illuminate\Database\Connection as BaseConnection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use Pelfox\LaravelBigQuery\Query\Grammar;
use Pelfox\LaravelBigQuery\Query\Processor;
use Throwable;

class Connection extends BaseConnection
{
    protected BigQueryClient $bigquery;

    protected Dataset $defaultDataset;

    protected string $sessionId = '';

    public function __construct($config = [])
    {
        $this->bigquery = new BigQueryClient([
            'keyFilePath' => $config['keyFilePath'] ?? ''
        ]);
        if (empty($config['dataset'])) {
            throw new Exception('"dataset" not found in database config');
        }
        $this->defaultDataset = $this->bigquery->dataset($config['dataset']);

        $this->database = '';

        $this->tablePrefix = $config['prefix'] ?? '';

        $this->config = $config;

        $this->useDefaultQueryGrammar();

        $this->useDefaultPostProcessor();
    }

    function getDefaultQueryGrammar(): Grammar
    {
        return (new Grammar)
            ->setConnection($this)
            ->setTablePrefix($this->tablePrefix)
            ->setSuffixTable($this->getConfig('suffix') ?: '');
    }

    protected function getDefaultPostProcessor(): Processor
    {
        return new Processor();
    }

    public function table($table, $as = null): Builder
    {
        return $this->query()->from($table, $as);
    }

    public function select($query, $bindings = [], $useReadPdo = true)
    {
        $res = $this->runQuery($query, $bindings);
        $data = [];
        foreach ($res->rows() as $row) {
            foreach ($row as $key => $value) {
                if ($value instanceof ValueInterface) {
                    $row[$key] = $value->formatAsString();
                }
                if ($value instanceof DateTimeInterface) {
                    $row[$key] = $value->format(DateTimeInterface::ATOM);
                }
            }
            $data[] = $row;
        }
        return $data;
    }

    public function runQuery($query, $bindings, $options = []): QueryResults
    {
        $query = $this->bindingParameters($query, $bindings);
        $qr = $this->bigquery->query($query, $this->getConnectionOptions($options))
            ->defaultDataset($this->defaultDataset);
        return $this->bigquery->runQuery($qr);
    }

    protected function getConnectionOptions($options)
    {
        if ($this->sessionId){
            $options['configuration']['query']['connectionProperties'][0] = [
                'value' => $this->sessionId,
                'key' => 'session_id'
            ];
        }
        return $options;
    }

    protected function bindingParameters($query, $bindings)
    {
        if (!$bindings) {
            return $query;
        }

        foreach ($bindings as $index => $value) {
            $bindings[$index] = $this->escapeValue($value);
        }

        return Str::replaceArray('?', $bindings, $query);
    }

    protected function escapeValue($value)
    {
        $type = gettype($value);
        switch ($type) {
            case 'object':
            case 'string':
                return '"' . str_replace('"', '\"', (string)$value) . '"';
            case 'double':
            case 'integer':
                return $value;
            case 'NULL':
                return 'null';
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'array':
                $values = [];
                foreach ($value as $v) {
                    $values = $this->escapeValue($v);
                }
                $join = implode(', ', $values);
                if (array_is_list($value)) {
                    return '[' . $join . ']';
                }
                return '(' . $join . ')';
            default:
                throw new Exception("'{$type}' type not support");
        }
    }

    public function statement($query, $bindings = [])
    {
        return $this->runQuery($query, $bindings)->isComplete();
    }

    public function affectingStatement($query, $bindings = [])
    {
        $rows = $this->runQuery($query, $bindings)->rows();
        $count = 0;
        foreach ($rows as $ignored) {
            $count++;
        }
        return $count;
    }

    public function beginTransaction()
    {
        $this->transactions++;

        $options['configuration']['query']['createSession'] = true;

        $this->sessionId = $this->runQuery('BEGIN TRANSACTION;', [], $options)
            ->job()->info()['statistics']['sessionInfo']['sessionId'] ?? '';

        $this->fireConnectionEvent('beganTransaction');
    }

    public function commit()
    {
        $this->runQuery('COMMIT TRANSACTION;', []);
    }


    public function transaction(Closure $callback, $attempts = 1)
    {
        for ($currentAttempt = 1; $currentAttempt <= $attempts; $currentAttempt++) {
            $this->beginTransaction();

            try {
                $callbackResult = $callback($this);
            } catch (Throwable $e) {
                $this->handleTransactionException(
                    $e, $currentAttempt, $attempts
                );
                continue;
            }

            try {
                if ($this->transactions == 1) {
                    $this->fireConnectionEvent('committing');
                    $this->commit();
                }

                if ($this->afterCommitCallbacksShouldBeExecuted()) {
                    $this->transactionsManager?->commit($this->getName());
                }
            } catch (Throwable $e) {
                $this->handleCommitTransactionException(
                    $e, $currentAttempt, $attempts
                );

                continue;
            } finally {
                $this->transactions = max(0, $this->transactions - 1);
            }

            $this->fireConnectionEvent('committed');

            return $callbackResult;
        }
    }

    public function rollBack($toLevel = null)
    {
        $this->runQuery('ROLLBACK TRANSACTION;', []);
    }
}
