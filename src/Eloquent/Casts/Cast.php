<?php

namespace Pelfox\LaravelBigQuery\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Pelfox\LaravelBigQuery\Types\RepeatedMode;
use Pelfox\LaravelBigQuery\Types\BaseType;

abstract class Cast implements CastsAttributes
{
    public function __construct(protected bool $repeated = false, protected string|array $methodSchema = '')
    {
    }

    public function get(Model $model, string $key, mixed $value, array $attributes, $recursive = false): mixed
    {
        if ($value instanceof BaseType) {
            return $value->get();
        }
        if ($this->repeated && !$recursive && is_null($value)) {
            return [];
        }
        if (is_null($value)) {
            return null;
        }
        if (!$this->repeated || $recursive) {
            return $this->getValue($model, $key, $value, $attributes);
        }
        $values = [];
        foreach ((array)$value as $item) {
            if (!is_null($arrValue = $this->get($model, '', $item, $attributes))) {
                $values[] = $arrValue;
            }
        }
        return $values;
    }

    abstract public function getValue(Model $model, string $key, mixed $value, array $attributes);

    public function set(Model $model, string $key, mixed $value, array $attributes, $recursive = false): mixed
    {
        if ($value instanceof BaseType || is_null($value)) {
            return $value;
        }

        if (!$this->repeated || $recursive) {
            return $this->setValue($model, $key, $value, $attributes);
        }

        $values = [];
        foreach ((array)$value as $item) {
            if (!is_null($arrValue = $this->set($model, '', $item, $attributes, true))) {
                $values[] = $arrValue;
            }
        }
        return new RepeatedMode($values);
    }

    abstract public function setValue(Model $model, string $key, mixed $value, array $attributes);
}
