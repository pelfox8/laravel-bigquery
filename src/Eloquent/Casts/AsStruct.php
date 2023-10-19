<?php

namespace Pelfox\LaravelBigQuery\Eloquent\Casts;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Pelfox\LaravelBigQuery\Types\StructType;

class AsStruct extends Cast
{

    public function getValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return $value;
    }

    /**
     * @throws Exception
     */
    public function setValue(Model $model, string $key, mixed $value, array $attributes)
    {
        return new StructType($value, $model->{$this->methodSchema}());
    }
}
