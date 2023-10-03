<?php

namespace Pelfox\LaravelBigQuery;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class LaravelBigQueryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Connection::resolverFor('bigquery', function ($connection, $database, $prefix, $config) {
            return new \Pelfox\LaravelBigQuery\Connection($config);
        });
    }

    public function boot(): void
    {
        Model::setConnectionResolver($this->app['db']);
        Model::setEventDispatcher($this->app['events']);
    }
}
