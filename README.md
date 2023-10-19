Laravel Bigquery
=========

This package allows you to use Query Builder and Eloquent for queries in Bigquery.

### Installation

```
composer require pelfox/laravel-bigquery
```

### Using

Add code in database config in section 'connections':

```php
'bigquery' => [
    'driver' => 'bigquery',
    'database' => '',
    'prefix' => '',
    'dataset' => 'replace on dataset from bigquery',
    'keyFilePath' => 'replace on path to service account config from google cloud',
],
```

Using in Query Builder or Eloquent:

```php
// Query Builder
DB::connection('bigquery')->table('table')->...
#for special dataset
DB::connection('bigquery')->table('dataset.table')->...

// Eloquent
class Table extends Model
{
    protected $connection = 'bigquery';
    #for special dataset
    protected $table = 'dataset.table';

    public $incrementing = false;
    public $timestamps = false;
}
```

### You may be use special Eloquent casts when which work correctly with data types Bigquery:

Namespace to casts: *\Pelfox\LaravelBigQuery\Eloquent\Casts*

| BigQuery type | Cast                |
|---------------|---------------------|
| String        | AsString::class     |
| Bytes         | AsBytes::class      |
| Integer       | AsInteger::class    |
| Float         | AsFloat::class      |
| Numeric       | AsNumeric::class    |
| BigNumeric    | AsBigNumeric::class |
| Boolean       | AsBoolean::class    |
| Timestamp     | AsTimestamp::class  |
| Datetime      | AsDateTime::class   |
| Date          | AsDate::class       |
| Time          | AsTime::class       |
| Struct        | AsStruct::class     |
| Json          | AsJson::class       |
| Geography     |                     |

If the field is repeated (mode is repeated), you need to pass an additional parameter for Cast ':1'

```php
'field' => AsString::class . ':1'
```

then an array of values can be passed to the field value

For struct data type necessary pass a method which return schema array

```php
'field' => AsString::class . ':0,getSchemaForFieldColumn'
```
schema for struct field should be the same as in bigquery or there will be an error when inserting data

Example a model with casts:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Pelfox\LaravelBigQuery\Eloquent\Casts\AsBigNumeric;
use Pelfox\LaravelBigQuery\Eloquent\Casts\AsBoolean;
use Pelfox\LaravelBigQuery\Eloquent\Casts\AsBytes;
use Pelfox\LaravelBigQuery\Eloquent\Casts\AsDate;
use Pelfox\LaravelBigQuery\Eloquent\Casts\AsDateTime;
use Pelfox\LaravelBigQuery\Eloquent\Casts\AsFloat;
use Pelfox\LaravelBigQuery\Eloquent\Casts\AsInteger;
use Pelfox\LaravelBigQuery\Eloquent\Casts\AsJson;
use Pelfox\LaravelBigQuery\Eloquent\Casts\AsNumeric;
use Pelfox\LaravelBigQuery\Eloquent\Casts\AsString;
use Pelfox\LaravelBigQuery\Eloquent\Casts\AsStruct;
use Pelfox\LaravelBigQuery\Eloquent\Casts\AsTime;
use Pelfox\LaravelBigQuery\Eloquent\Casts\AsTimestamp;
use Pelfox\LaravelBigQuery\Types\IntegerType;
use Pelfox\LaravelBigQuery\Types\StringType;

class Test extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'test';

    public $timestamps = false;

    protected $connection = 'bigquery';

    protected $fillable = [
        'string', 'integer', 'bytes', 'float', 'numeric', 'bignumeric',
        'boolean', 'timestamp', 'date', 'time', 'datetime',
        'record', 'json', 'strings', 'struct'
    ];

    protected $casts = [
        'string' => AsString::class,
        'strings' => AsString::class . ':1',
        'bytes' => AsBytes::class,
        'integer' => AsInteger::class,
        'float' => AsFloat::class,
        'numeric' => AsNumeric::class,
        'bignumeric' => AsBigNumeric::class,
        'boolean' => AsBoolean::class,
        'timestamp' => AsTimestamp::class,
        'date' => AsDate::class,
        'time' => AsTime::class,
        'datetime' => AsDateTime::class,
        'record' => AsStruct::class . ':0,getSchemaForRecord',
        'json' => AsJson::class,
        'struct' => AsStruct::class . ':0,getSchemaForStruct'
    ];

    public function getSchemaForRecord(): array
    {
        return [
            'string' => StringType::class
        ];
    }

    public function getSchemaForStruct(): array
    {
        return [
            'string' => StringType::class,
            'integer' => IntegerType::class,
            'names' => [StringType::class],
            'struct' => [
                'string' => StringType::class,
                'integer' => IntegerType::class,
            ],
            'array' => [
                [
                    'string' => StringType::class,
                    'integer' => IntegerType::class
                ]
            ]
        ];
    }
}
```
