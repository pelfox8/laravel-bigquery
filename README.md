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

// Eloquent
class Table extends Model
{
    protected $connection = 'bigquery';

    public $incrementing = false;
    public $timestamps = false;
}
```


To use Bigquery connection as the default, specify the bigquery value for DB_CONNECTION in the .env file:

```dotenv
DB_CONNECTION=bigquery
```

To use a different dataset instead of the one specified in the configuration  replace 'dataset' in names tables:

```php
// Query Builder
DB::connection('bigquery')->table('dataset.table')->...

// Eloquent
class Table extends Model
{
    protected $table = 'dataset.table';
}
```
