<?php

namespace Pelfox\LaravelBigQuery;

use Closure;
use Exception;
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\BigQuery\Dataset;
use Google\Cloud\BigQuery\QueryResults;
use Illuminate\Database\Connection as BaseConnection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use Pelfox\LaravelBigQuery\Facades\BigQuery;
use Pelfox\LaravelBigQuery\Query\Grammar;
use Pelfox\LaravelBigQuery\Query\Processor;
use Throwable;

class Connection extends BaseConnection
{
    protected BigQueryClient $bigquery;

    protected Dataset $defaultDataset;

    protected string $sessionId = '';

    /**
     * @throws Exception
     */
    public function __construct($config = [])
    {
        $this->bigquery = BigQuery::getFacadeRoot();

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

    /**
     * @throws Exception
     */
    public function select($query, $bindings = [], $useReadPdo = true): array
    {
        $res = $this->runQuery($query, $bindings);
        return (new ParseValue($res->info()))->getRows();
    }

    /**
     * @throws Exception
     */
    public function runQuery($query, $bindings = [], $options = []): QueryResults
    {
        $query = $this->bindingParameters($query, $bindings);
        $qr = $this->bigquery->query($query, $this->getConnectionOptions($options))
            ->defaultDataset($this->defaultDataset);
        return $this->bigquery->runQuery($qr);
    }

    protected function getConnectionOptions($options): array
    {
        if ($this->sessionId) {
            $options['configuration']['query']['connectionProperties'][0] = [
                'value' => $this->sessionId,
                'key' => 'session_id'
            ];
        }
        return $options;
    }

    /**
     * @throws Exception
     */
    protected function bindingParameters($query, $bindings): string
    {
        if (!$bindings) {
            return $query;
        }

        foreach ($bindings as $index => $value) {
            $bindings[$index] = Escape::any($value);
        }

        return Str::replaceArray('?', $bindings, $query);
    }

    /**
     * @throws Exception
     */
    public function statement($query, $bindings = []): bool
    {
        return $this->runQuery($query, $bindings)->isComplete();
    }

    /**
     * @throws Exception
     */
    public function affectingStatement($query, $bindings = []): int
    {
        $info = $this->runQuery($query, $bindings)->info();
        return (int)($info['numDmlAffectedRows'] ?? 0);
    }

    public function beginTransaction(): void
    {
        $this->transactions++;

        $options['configuration']['query']['createSession'] = true;

        $this->sessionId = $this->runQuery('BEGIN TRANSACTION;', [], $options)
            ->job()->info()['statistics']['sessionInfo']['sessionId'] ?? '';

        $this->fireConnectionEvent('beganTransaction');
    }

    public function commit(): void
    {
        $this->runQuery('COMMIT TRANSACTION;');
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

    public function rollBack($toLevel = null): void
    {
        $this->runQuery('ROLLBACK TRANSACTION;');
    }
}
