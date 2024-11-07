<?php

namespace Pelfox\LaravelBigQuery;

use DateTime;
use Exception;

class ParseValue
{

    public function __construct(protected $bigqueryInfo)
    {
    }

    /**
     * @throws Exception
     */
    public function getRows()
    {
        if (empty($this->bigqueryInfo['rows'])) {
            return [];
        }
        $fields = $this->bigqueryInfo['schema']['fields'] ?? [];
        $data = [];
        foreach ($this->bigqueryInfo['rows'] as $index => $row) {
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
    protected function getRepeatedValue($value, $schema)
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
}
