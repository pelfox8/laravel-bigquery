<?php

namespace Pelfox\LaravelBigQuery\Types;

use Exception;

class StructType extends BaseType
{
    /**
     * @throws Exception
     */
    public function __construct($value, protected array $schema = [])
    {
        parent::__construct((array)$value);
    }

    /**
     * @throws Exception
     */
    public function get(): array
    {
        return $this->formattedValue();
    }

    /**
     * @throws Exception
     */
    public function formattedQueryValue(): string
    {
        $values = [];

        foreach ($this->formattedValue() as $key => $value) {
            $values[] = $this->buildValue($value?->formattedQueryValue(), $key);
        }
        if ($values) {
            return 'struct(' . implode(', ', $values) . ')';
        } else {
            return 'null';
        }
    }


    /**
     * @throws Exception
     */
    protected function formattedValue(): array
    {
        $values = [];

        foreach ($this->schema as $column => $type) {
            $values[$column] = null;

            if (!isset($this->value[$column])) {
                continue;
            }

            if (!is_array($type) && $this->checkAsType($type)) {
                $values[$column] = new $type($this->value[$column]);
                continue;
            }

            if (!array_is_list($type)) {
                $values[$column] = new static($this->value[$column], $type);
                continue;
            }

            if (empty($type[0]) && (is_array($type[0]) || $this->checkAsType($type[0]))) {
                $class = is_array($type[0]) ? __CLASS__ : $type[0];
                $value = array_map(function ($v) use ($class, $type) {
                    return new $class($v, $type[0]);
                }, array_values((array)$this->value[$column]));
                $values[$column] = new RepeatedMode($value);
            }
        }

        return $values;
    }

    protected function checkAsType($class): bool
    {
        return is_a($class, BaseType::class, true);
    }

    protected function buildValue($value, $key): string
    {
        $value = is_null($value) ? 'null' : $value;
        return "$value as `$key`";
    }
}
