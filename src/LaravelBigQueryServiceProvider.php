<?php

namespace Pelfox\LaravelBigQuery;

use EinarHansen\Cache\CacheItemPool;
use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;

class LaravelBigQueryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('bigquery', function () {
            return new BigQueryClient([
                'keyFilePath' => config('database.connections.bigquery.keyFilePath'),
                'authCache' => $this->getAuthCache()
            ]);
        });

        $this->app->resolving('db', function ($db) {
            $db->extend('bigquery', function ($config, $name) {
                $config['name'] = $name;

                return new Connection($config);
            });
        });
    }

    /**
     * @throws BindingResolutionException
     */
    protected function getAuthCache(): CacheItemPoolInterface
    {
        return new CacheItemPool($this->app->make(Repository::class));
    }

    public function boot(): void
    {
        Model::setConnectionResolver($this->app['db']);
        Model::setEventDispatcher($this->app['events']);
    }
}
