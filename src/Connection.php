<?php

namespace Pelfox\LaravelBigQuery;

use Closure;
use DateTime;
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
use Pelfox\LaravelBigQuery\Types\BaseType;
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
        return $this->getRows($res->info());
    }

    /**
     * @throws Exception
     */
    protected function getRows($info): array
    {
        if (empty($info['rows'])) {
            return [];
        }
        $fields = $info['schema']['fields'] ?? [];
        $data = [];
        foreach ($info['rows'] as $index => $row) {
            foreach ($row['f'] as $key => $value) {
                $field = $fields[$key];
                $data[$index][$field['name']] = $this->getValue($value, $field);
            }
        }
        return $data;
    }

    /**
     * @throws Exception
     */
    protected function getRepeatedValue($value, $schema): array
    {
        unset($schema['mode']);

        $repeatedValues = [];
        foreach ($value as $repeatedValue) {
            $repeatedValues[] = $this->getValue($repeatedValue, $schema);
        }
        return $repeatedValues;
    }

    /**
     * @throws Exception
     */
    protected function getValue($value, $schema): mixed
    {
        $value = $value['v'];

        if (isset($schema['mode'])) {
            if ($schema['mode'] === 'REPEATED') {
                return $this->getRepeatedValue($value, $schema);
            }

            if ($schema['mode'] === 'NULLABLE' && $value === null) {
                return null;
            }
        }

        return match ($schema['type']) {
            'BOOLEAN' => $value === 'true',
            'INTEGER' => (int)$value,
            'FLOAT' => (float)$value,
            'BYTES' => base64_decode($value),
            'TIMESTAMP' => $this->geTimestampValue($value),
            'RECORD' => $this->getRecordValue($value, $schema['fields']),
            'JSON' => $value ? json_decode($value, true) : $value,
            default => (string)$value,
        };
    }

    /**
     * @throws Exception
     */
    protected function geTimestampValue($value): string
    {
        if (strpos($value, 'E')) {
            list($value, $exponent) = explode('E', $value);
            list($firstDigit, $remainingDigits) = explode('.', $value);

            if (strlen($remainingDigits) > $exponent) {
                $value = $firstDigit . substr_replace($remainingDigits, '.', $exponent, 0);
            } else {
                $value = $firstDigit . str_pad($remainingDigits, $exponent, '0') . '.0';
            }
        }

        $parts = explode('.', $value);
        $unixTimestamp = $parts[0];
        $microSeconds = $parts[1] ?? 0;

        $dateTime = new DateTime("@$unixTimestamp");

        if ($microSeconds > 0 && $unixTimestamp[0] === '-') {
            $microSeconds = 1000000 - (int)str_pad($microSeconds, 6, '0');
            $dateTime->modify('-1 second');
        }

        return (new DateTime(
            sprintf(
                '%s.%s+00:00',
                $dateTime->format('Y-m-d H:i:s'),
                $microSeconds
            )
        ))->format('Y-m-d H:i:s.uP');
    }

    /**
     * @throws Exception
     */
    protected function getRecordValue($value, $schema): array
    {
        $record = [];

        foreach ($value['f'] as $key => $val) {
            $record[$schema[$key]['name']] = $this->getValue($val, $schema[$key]);
        }

        return $record;
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
            $bindings[$index] = $this->escapeValue($value);
        }

        return Str::replaceArray('?', $bindings, $query);
    }

    /**
     * @throws Exception
     */
    protected function escapeValue($value): string
    {
        $type = gettype($value);
        return match ($type) {
            'object' => $value instanceof BaseType ? $value->formattedQueryValue() : (string)$value,
            'string' => '"' . str_replace('"', '\"', (string)$value) . '"',
            'double',
            'integer' => $value,
            'NULL' => 'null',
            'boolean' => $value ? 'true' : 'false',
            'array' => $this->escapeArrayValue($value),
            default => throw new Exception("'$type' type not support"),
        };
    }

    /**
     * @throws Exception
     */
    protected function escapeArrayValue($value): string
    {
        $values = [];
        foreach ($value as $v) {
            $values = $this->escapeValue($v);
        }
        $join = implode(', ', $values);
        if (array_is_list($value)) {
            return '[' . $join . ']';
        }
        return '(' . $join . ')';
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
