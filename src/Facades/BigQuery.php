<?php

namespace Pelfox\LaravelBigQuery\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Google\Cloud\BigQuery\QueryJobConfiguration query($query, array $options = [])
 * @method static \Google\Cloud\BigQuery\QueryJobConfiguration queryConfig($query, array $options = [])
 * @method static \Google\Cloud\BigQuery\Exception\JobException runQuery(\Google\Cloud\BigQuery\JobConfigurationInterface $query, array $options = [])
 * @method static \Google\Cloud\BigQuery\Job startQuery(\Google\Cloud\BigQuery\JobConfigurationInterface $query, array $options = [])
 * @method static \Google\Cloud\BigQuery\Job job($id, array $options = [])
 * @method static \Google\Cloud\Core\Iterator\ItemIterator<\Google\Cloud\BigQuery\Job> jobs(array $options = [])
 * @method static \Google\Cloud\BigQuery\Dataset dataset($id, $projectId = null)
 * @method static \Google\Cloud\Core\Iterator\ItemIterator<\Google\Cloud\BigQuery\Dataset> datasets(array $options = [])
 * @method static \Google\Cloud\BigQuery\Dataset createDataset($id, array $options = [])
 * @method static \Google\Cloud\BigQuery\Job runJob(\Google\Cloud\BigQuery\JobConfigurationInterface $config, array $options = [])
 * @method static \Google\Cloud\BigQuery\Job startJob(\Google\Cloud\BigQuery\JobConfigurationInterface $config, array $options = [])
 * @method static \Google\Cloud\BigQuery\Bytes bytes($value)
 * @method static \Google\Cloud\BigQuery\Date date(\DateTimeInterface $value)
 * @method static \Google\Cloud\Core\Int64 int64($value)
 * @method static \Google\Cloud\BigQuery\Time time(\DateTimeInterface $value)
 * @method static \Google\Cloud\BigQuery\Timestamp timestamp(\DateTimeInterface $value)
 * @method static \Google\Cloud\BigQuery\Numeric numeric($value)
 * @method static \Google\Cloud\BigQuery\BigNumeric bigNumeric($value)
 * @method static \Google\Cloud\BigQuery\Geography geography($value)
 * @method static string getServiceAccount(array $options = [])
 * @method static \Google\Cloud\BigQuery\CopyJobConfiguration copy(array $options = [])
 * @method static \Google\Cloud\BigQuery\ExtractJobConfiguration extract(array $options = [])
 * @method static \Google\Cloud\BigQuery\LoadJobConfiguration load(array $options = [])
 */
class BigQuery extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'bigquery';
    }
}
